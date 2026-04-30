-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2026 at 09:48 AM
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
(1, 3, 'Diwa', 0, '2026-04-25 13:01:43'),
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
(1, 'hello', 'wassup', 'Ala-Uli', 'barangay', 1, '2026-04-25', '2026-04-25 15:46:40'),
(2, 'Water Interruption', 'Good Day! There will be a scheduled water interruption on April 27, 2016 from 6:00 am to 1:oo pm. We advise everyone to secure enough water to last until the interruption ends.', '', 'everyone', 1, '2026-04-26', '2026-04-26 09:49:11'),
(3, 'Water Reading Schedule', 'Good day! We would like to inform you that the scheduled water reading will be held today wiii', '', 'everyone', 1, '2026-04-28', '2026-04-28 01:07:39'),
(4, 'Librang tubig', 'wassup doi libre tubig for this month', '', 'everyone', 1, '2026-04-28', '2026-04-28 13:09:05'),
(5, 'huwat', 'goodness graciousss ikaww ung nagshopliftt!!!!', '', 'everyone', 1, '2026-04-30', '2026-04-30 05:44:39'),
(6, 'wat es that', 'dfghjklpoiuytrsaxcvbnm,', '', 'everyone', 1, '2026-04-30', '2026-04-30 05:45:05'),
(7, 'Water Interruption', 'Good day everyone! There will be scheduled water interruption on May 1, 2026 from 8:00am to 10:00am. In reson of there will water tubes that needed replacement. Thank you!', 'Alauli', 'barangay', 1, '2026-05-01', '2026-04-30 07:03:16');

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
(1, 6, 'Others', 'kulay matcha yung tubig doi', 'awit sah may flavor, aucn k bukas sah', 'in_progress', '2026-04-25 15:49:38', '2026-04-25 23:53:16'),
(2, 7, 'Meter Problem', 'kuya natanggal', 'awit doi, gew pakabit ko', 'resolved', '2026-04-26 11:09:23', '2026-04-26 20:47:34'),
(3, 7, 'Payment Concern', 'pede b hulugan', 'awit doi ano kmi homecredit? bawal', 'resolved', '2026-04-26 12:56:01', '2026-04-27 03:02:00'),
(4, 7, 'Others', 'wadup', 'wassup sah', 'in_progress', '2026-04-26 19:00:29', '2026-04-27 03:02:17'),
(5, 6, 'Water Interruption', 'doi ala kaming tubig', 'wala akong apke', 'resolved', '2026-04-28 13:03:24', '2026-04-28 21:12:52');

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
(1, 6, 1, 'INV-20260425-3121', 123, 10.00, 1230.00, '2026-05-10', 'paid', 1, '2026-04-25 15:47:36'),
(2, 7, 4, 'INV-20260426-1596', 123, 0.00, 2600.04, '2026-05-11', 'paid', 1, '2026-04-26 09:53:25'),
(3, 6, 5, 'INV-20260426-2754', 2, 0.00, 165.00, '2026-05-11', 'paid', 1, '2026-04-26 19:49:39'),
(4, 7, 6, 'INV-20260427-9509', 3, 0.00, 165.00, '2026-05-12', 'paid', 1, '2026-04-26 22:43:38'),
(5, 7, 7, 'INV-20260427-7994', 0, 0.00, 165.00, '2026-05-12', 'paid', 1, '2026-04-27 13:48:34'),
(6, 6, 8, 'INV-20260427-6704', 3, 0.00, 165.00, '2026-05-12', 'unpaid', 1, '2026-04-27 16:13:37'),
(7, 7, 9, 'INV-20260428-9080', 4, 0.00, 165.00, '2026-05-13', 'paid', 1, '2026-04-28 13:10:40'),
(8, 7, 10, 'INV-20260430-8471', 10, 0.00, 165.00, '2026-05-15', 'unpaid', 1, '2026-04-30 07:04:51');

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
(3, 0, 3, 125, 456, 331, '2026-04-26', 'completed', '2026-04-26 02:51:09'),
(4, 7, 3, 0, 123, 123, '2026-04-26', 'completed', '2026-04-26 03:53:20'),
(5, 6, 8, 123, 125, 2, '2026-04-26', 'completed', '2026-04-26 19:48:11'),
(6, 7, 3, 123, 126, 3, '2026-04-27', 'completed', '2026-04-26 22:42:24'),
(7, 7, 3, 126, 126, 0, '2026-04-27', 'completed', '2026-04-27 13:45:51'),
(8, 6, 8, 125, 128, 3, '2026-04-27', 'completed', '2026-04-27 16:08:45'),
(9, 7, 3, 126, 130, 4, '2026-04-28', 'completed', '2026-04-28 13:05:53'),
(10, 7, 3, 130, 140, 10, '2026-04-30', 'completed', '2026-04-30 06:57:00');

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
  `type` varchar(50) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `role_target`, `title`, `message`, `status`, `created_at`, `type`, `link`) VALUES
(1, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:47:39', 'reading', NULL),
(2, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:48:08', 'reading', NULL),
(3, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 02:48:29', 'complaint', NULL),
(4, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 02:49:00', 'complaint', NULL),
(5, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:49:54', 'reading', NULL),
(6, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:50:20', 'reading', NULL),
(7, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:50:50', 'reading', NULL),
(8, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:11', 'reading', NULL),
(9, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:14', 'reading', NULL),
(10, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:16', 'reading', NULL),
(11, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:17', 'reading', NULL),
(12, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:17', 'reading', NULL),
(13, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:17', 'reading', NULL),
(14, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:17', 'reading', NULL),
(15, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:17', 'reading', NULL),
(16, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:18', 'reading', NULL),
(17, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:18', 'reading', NULL),
(18, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:18', 'reading', NULL),
(19, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:18', 'reading', NULL),
(20, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:18', 'reading', NULL),
(21, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 02:51:36', 'complaint', NULL),
(22, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 02:51:42', 'complaint', NULL),
(23, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 02:51:43', 'complaint', NULL),
(24, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 02:51:44', 'complaint', NULL),
(25, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 02:51:44', 'complaint', NULL),
(26, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 02:51:44', 'complaint', NULL),
(27, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'read', '2026-04-26 02:51:46', 'complaint', NULL),
(28, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'read', '2026-04-26 02:51:49', 'complaint', NULL),
(29, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 03:16:56', 'reading', NULL),
(30, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 03:22:40', 'reading', NULL),
(31, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 03:22:57', 'reading', NULL),
(32, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 03:23:17', 'reading', NULL),
(33, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 03:23:21', 'reading', NULL),
(34, 3, 'admin', 'Meter Reading Completed', 'Field agent submitted meter reading.', 'unread', '2026-04-26 03:53:20', 'reading', NULL),
(35, 7, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'read', '2026-04-26 05:05:16', 'complaint', NULL),
(36, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'read', '2026-04-26 05:08:21', 'complaint', NULL),
(37, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'read', '2026-04-26 05:52:53', 'complaint', NULL),
(38, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 09:44:13', 'complaint', NULL),
(39, 7, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 09:49:46', 'complaint', NULL),
(40, 7, 'admin', 'New Complaint Submitted', 'Complaint Type: Water Interruption | Description: akala ko ba 27 pa bat ngayon palang wala na kaming tubig doi', 'read', '2026-04-26 09:51:46', 'complaint', NULL),
(41, 7, 'user', 'New Bill Released', 'A new billing invoice has been released. Please check your billing page.', 'read', '2026-04-26 09:53:25', 'bill', NULL),
(42, 7, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'read', '2026-04-26 09:55:05', 'complaint', NULL),
(43, 7, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 09:56:36', 'complaint', NULL),
(44, 7, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 09:59:10', 'complaint', NULL),
(45, 7, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 10:48:24', 'complaint', NULL),
(46, 7, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 10:49:56', 'complaint', NULL),
(47, 7, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 10:54:05', 'complaint', NULL),
(48, 7, 'admin', 'New Complaint Submitted', 'Complaint Type: Billing Concern | Description: kulang bayad ko hehe', 'unread', '2026-04-26 11:02:27', 'complaint', 'complaints_admin.php'),
(49, 7, 'user', 'Complaint Successfully Sent', 'Your complaint about \'Billing Concern\' was submitted successfully. You can review its status anytime.', 'read', '2026-04-26 11:02:27', 'complaint', 'user_complaints.php'),
(50, 7, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 11:03:53', 'complaint', NULL),
(51, 7, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 11:04:11', 'complaint', NULL),
(52, 7, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 11:08:58', 'complaint', NULL),
(53, 7, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 11:09:00', 'complaint', NULL),
(54, 7, 'admin', 'New Complaint Submitted', 'Complaint Type: Meter Problem | Description: kuya natanggal', 'unread', '2026-04-26 11:09:23', 'complaint', 'complaints_admin.php'),
(55, 7, 'user', 'Complaint Successfully Sent', 'Your complaint about \'Meter Problem\' was submitted successfully. You can review its status anytime.', 'read', '2026-04-26 11:09:23', 'complaint', 'user_complaints.php'),
(56, 7, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'read', '2026-04-26 12:48:15', 'complaint', NULL),
(57, 7, 'admin', 'New Complaint Submitted', 'Complaint Type: Payment Concern | Description: pede b hulugan', 'read', '2026-04-26 12:56:01', 'complaint', 'complaints_admin.php'),
(58, 7, 'user', 'Complaint Successfully Sent', 'Your complaint about \'Payment Concern\' was submitted successfully. You can review its status anytime.', 'read', '2026-04-26 12:56:01', 'complaint', 'user_complaints.php'),
(59, 7, 'admin', 'New Complaint Submitted', 'Complaint Type: Others | Description: wadup', 'read', '2026-04-26 19:00:29', 'complaint', 'complaints_admin.php'),
(60, 7, 'user', 'Complaint Successfully Sent', 'Your complaint about \'Others\' was submitted successfully. You can review its status anytime.', 'read', '2026-04-26 19:00:29', 'complaint', 'user_complaints.php'),
(61, 7, 'user', 'Complaint Update', 'Your complaint \'Payment Concern\' has been reviewed. Status: resolved. Click to view reply.', 'read', '2026-04-26 19:02:00', 'complaint', 'user_complaints.php'),
(62, 7, 'user', 'Complaint Update', 'Your complaint \'Others\' has been reviewed. Status: in_progress. Click to view reply.', 'read', '2026-04-26 19:02:17', 'complaint', 'user_complaints.php'),
(63, 6, 'admin', 'User Profile Updated', 'Jewel Baciles updated profile information.', 'unread', '2026-04-26 19:43:36', NULL, NULL),
(64, 6, 'admin', 'User Profile Updated', 'Jewel Baciles updated profile information.', 'unread', '2026-04-26 19:45:19', NULL, NULL),
(65, 8, 'admin', 'Meter Reading Completed', 'Field agent submitted meter reading.', 'unread', '2026-04-26 19:48:11', 'reading', NULL),
(66, 6, 'user', 'New Bill Released', 'A new water bill has been released. Please check your billing page.', 'read', '2026-04-26 19:49:39', 'bill', 'user_billing.php'),
(67, 7, 'admin', 'User Profile Updated', 'Kier Baciles updated profile information.', 'unread', '2026-04-26 19:58:29', 'profile', NULL),
(68, 7, 'admin', 'User Profile Updated', 'Kier Baciles updated profile information.', 'unread', '2026-04-26 19:58:37', 'profile', NULL),
(69, 6, 'user', 'Payment Verified', 'Your payment has been verified. Receipt is now available.', 'read', '2026-04-26 20:08:03', 'payment', NULL),
(70, 6, 'user', 'Payment Rejected', 'Your payment was rejected. Please review and submit again.', 'read', '2026-04-26 20:08:03', 'payment', NULL),
(71, 6, 'user', 'Payment Verified', 'Your payment has been verified. Receipt is now available.', 'read', '2026-04-26 22:14:12', 'payment', NULL),
(72, 6, 'user', 'Payment Rejected', 'Your payment was rejected. Please review and submit again.', 'read', '2026-04-26 22:14:12', 'payment', NULL),
(73, 6, 'user', 'Payment Verified', 'Your payment has been verified. Receipt is now available.', 'read', '2026-04-26 22:37:50', 'payment', 'user_history.php'),
(74, 3, 'admin', 'Meter Reading Completed', 'Field agent submitted meter reading.', 'unread', '2026-04-26 22:42:24', 'reading', NULL),
(75, 7, 'user', 'New Bill Released', 'A new water bill has been released. Please check your billing page.', 'read', '2026-04-26 22:43:38', 'bill', 'user_billing.php'),
(76, 7, 'user', 'Payment Rejected', 'Your payment was rejected. Please review and submit again.', 'read', '2026-04-26 22:59:38', 'payment', 'user_payments.php'),
(77, 7, 'user', 'Payment Verified', 'Your payment has been verified. Receipt is now available.', 'read', '2026-04-26 23:55:48', 'payment', 'user_history.php'),
(78, 7, 'admin', 'User Profile Updated', 'Kier Baciles updated profile information.', 'read', '2026-04-27 12:38:58', 'profile', NULL),
(79, 7, 'admin', 'User Profile Updated', 'Kier Baciles updated profile information.', 'unread', '2026-04-27 13:35:11', 'profile', NULL),
(80, 7, 'admin', 'User Profile Updated', 'Kier Baciles updated profile information.', 'unread', '2026-04-27 13:35:31', 'profile', NULL),
(81, 7, 'admin', 'User Profile Updated', 'Kier Baciles updated profile information.', 'unread', '2026-04-27 13:35:45', 'profile', NULL),
(82, 3, 'admin', 'Meter Reading Completed', 'Field agent submitted meter reading.', 'unread', '2026-04-27 13:45:51', 'reading', NULL),
(83, 7, 'user', 'New Bill Released', 'A new water bill has been released. Please check your billing page.', 'read', '2026-04-27 13:48:34', 'bill', 'user_billing.php'),
(84, 8, 'admin', 'Meter Reading Completed', 'Field agent submitted meter reading.', 'read', '2026-04-27 16:08:45', 'reading', NULL),
(85, 6, 'user', 'New Bill Released', 'A new water bill has been released. Please check your billing page.', 'unread', '2026-04-27 16:13:37', 'bill', 'user_billing.php'),
(86, 7, 'user', 'Payment Verified', 'Your payment has been verified. Receipt is now available.', 'read', '2026-04-28 05:20:02', 'payment', 'user_history.php'),
(87, 6, 'admin', 'New Complaint Submitted', 'Complaint Type: Water Interruption | Description: doi ala kaming tubig', 'read', '2026-04-28 13:03:24', 'complaint', 'complaints_admin.php'),
(88, 6, 'user', 'Complaint Successfully Sent', 'Your complaint about \'Water Interruption\' was submitted successfully. You can review its status anytime.', 'unread', '2026-04-28 13:03:24', 'complaint', 'user_complaints.php'),
(89, 3, 'admin', 'Meter Reading Completed', 'Field agent submitted meter reading.', 'unread', '2026-04-28 13:05:53', 'reading', NULL),
(90, 7, 'user', 'New Bill Released', 'A new water bill has been released. Please check your billing page.', 'unread', '2026-04-28 13:10:40', 'bill', 'user_billing.php'),
(91, 6, 'user', 'Complaint Update', 'Your complaint \'Water Interruption\' has been reviewed. Status: resolved. Click to view reply.', 'unread', '2026-04-28 13:12:52', 'complaint', 'user_complaints.php'),
(92, 3, 'admin', 'Meter Reading Completed', 'Field agent submitted meter reading.', 'unread', '2026-04-30 06:57:00', 'reading', NULL),
(93, 7, 'user', 'New Bill Released', 'A new water bill has been released. Please check your billing page.', 'unread', '2026-04-30 07:04:51', 'bill', 'user_billing.php'),
(94, 6, 'user', 'Payment Verified', 'Your payment has been verified. Receipt is now available.', 'read', '2026-04-30 07:21:50', 'payment', NULL),
(95, 6, 'user', 'Payment Rejected', 'Your payment was rejected. Please review and submit again.', 'unread', '2026-04-30 07:21:50', 'payment', 'user_payments.php'),
(96, 7, 'user', 'Payment Verified', 'Your payment has been verified. Receipt is now available.', 'unread', '2026-04-30 07:22:08', 'payment', 'user_history.php');

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
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `proof_image` varchar(255) DEFAULT NULL,
  `qr_token` varchar(100) DEFAULT NULL,
  `qr_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `invoice_id`, `user_id`, `transaction_no`, `amount`, `payment_method`, `reference_no`, `status`, `verified_by`, `paid_at`, `proof_image`, `qr_token`, `qr_expires_at`) VALUES
(1, 1, 6, 'TRX-20260425-3581', 1230.00, 'Bank Transfer', 'TRX-20260425-3581', 'verified', 2, '2026-04-25 15:49:14', NULL, NULL, NULL),
(2, 2, 7, 'TRX-20260426-1764', 2600.00, 'Maya', 'TRX-20260426-1764', 'verified', 2, '2026-04-26 09:57:53', NULL, NULL, NULL),
(3, 3, 6, 'TRX-20260426-7661', 165.55, 'Cash', 'TRX-20260426-7661', 'rejected', 2, '2026-04-26 20:04:59', NULL, NULL, NULL),
(4, 3, 6, 'TRX-20260426-9788', 165.00, 'GCash', 'TRX-20260426-9788', 'rejected', 2, '2026-04-26 20:09:30', NULL, NULL, NULL),
(5, 3, 6, 'TRX-20260427-9089', 165.00, 'GCash', 'TRX-20260427-9089', 'verified', 2, '2026-04-26 22:16:06', 'proof_6_1777241766.png', 'QR-7daa7fb64e33225c', '2026-04-27 00:31:06'),
(6, 4, 7, 'TRX-20260427-6797', 165.00, 'Maya', 'TRX-20260427-6797', 'rejected', 2, '2026-04-26 22:45:31', 'proof_7_1777243531.jpg', 'QR-dd37ae477ea51b5e', '2026-04-27 01:00:31'),
(7, 4, 7, 'TRX-20260427-6978', 165.00, 'Bank Transfer', 'TRX-20260427-6978', 'verified', 2, '2026-04-26 23:55:03', 'proof_7_1777247703.jpg', 'QR-bb6049ec06fab771', '2026-04-27 02:10:03'),
(8, 5, 7, 'TRX-20260428-2587', 165.00, 'Bank Transfer', 'TRX-20260428-2587', 'verified', 2, '2026-04-28 05:18:47', 'proof_7_1777353527.jpg', 'QR-33f7456524add47c', '2026-04-28 07:33:47'),
(9, 7, 7, 'TRX-20260429-3693', 165.00, 'Maya', 'TRX-20260429-3693', 'verified', 2, '2026-04-29 04:09:13', 'proof_7_1777435753.jpg', 'QR-edd2a74df9633952', '2026-04-29 06:24:13'),
(10, 6, 6, 'TRX-20260430-9017', 165.00, 'GCash', 'TRX-20260430-9017', 'rejected', 2, '2026-04-30 07:16:50', 'proof_6_1777533410.png', 'QR-5dedae85a31a2439', '2026-04-30 09:31:50');

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
(1, 1, 'REC-20260425-2218', '2026-04-25 15:50:27'),
(2, 2, 'REC-20260426-8522', '2026-04-26 10:00:21'),
(3, 5, 'REC-20260427-9250', '2026-04-26 22:37:50'),
(4, 7, 'REC-20260427-9533', '2026-04-26 23:55:48'),
(5, 8, 'REC-20260428-9854', '2026-04-28 05:20:02'),
(6, 9, 'REC-20260430-8022', '2026-04-30 07:22:08');

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
(2, 'Hello', 'Ganda', 'accountant@gmail.com', '09222222222', '123', 'accountant', NULL, NULL, NULL, 'active', '2026-04-25 13:01:43', NULL, 'avatar_2_1777396864.jpg'),
(3, 'Field', 'Agent', 'agent@gmail.com', '09333333333', '123', 'agent', NULL, 'Barangay 1', 'Area A', 'active', '2026-04-25 13:01:43', NULL, 'default.png'),
(6, 'Jewel', 'Baciles', 'jkmcbaciles24@bpsu.edu.ph', '03483022253', '159asd', 'user', '24-02397', 'Alauli', 'ewan', 'active', '2026-04-25 14:59:51', NULL, 'default.png'),
(7, 'Kier', 'Baciles', 'bacilesjewel10@gmail.com', '09279554267', '456123', 'user', '24-03385', 'Diwa', 'wiwoo', 'active', '2026-04-25 23:30:48', 'USR-20260426-7233', 'default.png'),
(8, 'budoy', 'buday', 'agent2@gmail.com', '1235789510', '147', 'agent', '', '', '', 'active', '2026-04-26 03:21:52', NULL, 'default.png'),
(9, 'nueve', 'Baciles-Acar', 'nueve@gmail.com', '09112233344', '789asd', 'user', '07-15005', 'Pantingan', 'howaw', 'active', '2026-04-28 04:48:29', 'USR-20260428-4118', 'default.png'),
(10, 'Robert ', 'Dajero', 'robert@gmail.com', '0912358469', '789123', 'user', '24-8520', 'Alauli', 'huwat', 'active', '2026-04-30 06:53:02', 'USR-20260430-5818', 'default.png');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `meter_readings`
--
ALTER TABLE `meter_readings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
