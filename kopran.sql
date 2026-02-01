-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 30, 2026 at 01:01 PM
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
-- Database: `kopran`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_trail`
--

CREATE TABLE `audit_trail` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `audit_trail`
--

INSERT INTO `audit_trail` (`id`, `user_id`, `action`, `details`, `ip_address`, `timestamp`) VALUES
(1, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-28 00:21:50'),
(2, 'test.admin', 'Print SOP', 'Printed SOP: SOP-IT-005 - IT Security Policy (Department: IT, Format: Full View)', '::1', '2026-01-28 00:25:19'),
(3, 'test.admin', 'Print SOP', 'Printed SOP: SOP-IT-005 - IT Security Policy (Department: IT, Format: Full View)', '::1', '2026-01-28 00:36:30'),
(4, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-28 00:43:51'),
(5, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-28 00:45:19'),
(6, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-28 00:51:14'),
(7, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-28 00:57:24'),
(8, 'qa.test', 'Failed Login', 'User not found or inactive', '::1', '2026-01-28 01:00:06'),
(9, 'qa.test', 'Failed Login', 'User not found or inactive', '::1', '2026-01-28 01:00:23'),
(10, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-28 01:00:58'),
(11, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-28 01:33:04'),
(12, 'test.admin', 'Print SOP', 'Printed SOP: SOP-IT-005 - IT Security Policy (Department: ALL, Format: Full View)', '::1', '2026-01-28 01:44:58'),
(13, 'test.admin', 'Upload SOP', 'Uploaded SOP: SOP-MF-001 - Multi-Format Test', '::1', '2026-01-28 01:50:28'),
(14, 'test.admin', 'Upload Format', 'Uploaded Format \'Checklist\' for SOP: SOP-MF-002', '::1', '2026-01-28 01:52:10'),
(15, 'test.admin', 'Upload Format', 'Uploaded Format \'Flowchart\' for SOP: SOP-MF-002', '::1', '2026-01-28 01:52:10'),
(16, 'test.admin', 'Upload SOP', 'Uploaded SOP: SOP-MF-002 - Multi-Format Test V2', '::1', '2026-01-28 01:52:10'),
(17, 'test.admin', 'Add Format', 'Added format \'Annexure 3\' to SOP: SOP-MF-001', '::1', '2026-01-28 02:23:20'),
(18, 'test.admin', 'Delete Format', 'Deleted format from SOP: SOP-MF-001', '::1', '2026-01-28 02:23:29'),
(19, 'test.admin', 'Add Format', 'Added format \'test\' to SOP: SOP-MF-001', '::1', '2026-01-28 02:24:17'),
(20, 'test.admin', 'Add Format', 'Added format \'it sop fomate new\' to SOP: SOP-IT-005', '::1', '2026-01-28 02:25:57'),
(21, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-IT-005_IT_Security_Policy.pdf)', '::1', '2026-01-28 02:40:16'),
(22, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-IT-005_IT_Security_Policy.pdf)', '::1', '2026-01-28 02:40:35'),
(23, 'test.admin', 'Failed Login', 'Invalid password', '::1', '2026-01-28 11:39:05'),
(24, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-28 11:39:29'),
(25, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-28 11:44:23'),
(26, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-MF-002_1769545330.pdf)', '::1', '2026-01-28 11:44:40'),
(27, 'test.admin', 'Create User', 'Created user: Bhagyeshp (Bhagyesh Patil) with role: user', '::1', '2026-01-28 11:47:43'),
(28, 'Bhagyeshp', 'Login', 'User logged in successfully', '::1', '2026-01-28 11:47:55'),
(29, 'Bhagyeshp', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-IT-005_IT_Security_Policy.pdf)', '::1', '2026-01-28 11:48:01'),
(30, 'Bhagyeshp', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-QA-010_QA_Handbook.pdf)', '::1', '2026-01-28 11:48:13'),
(31, 'test.admin', 'Failed Login', 'Invalid password', '::1', '2026-01-28 11:48:31'),
(32, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-28 11:48:50'),
(33, 'test.admin', 'Failed Login', 'Invalid password', '::1', '2026-01-28 11:49:57'),
(34, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-28 11:50:13'),
(35, 'test.admin', 'Failed Login', 'Invalid password', '::1', '2026-01-28 11:51:12'),
(36, 'test.admin', 'Failed Login', 'Invalid password', '::1', '2026-01-28 11:51:20'),
(37, 'test.admin', 'Failed Login', 'Invalid password', '::1', '2026-01-28 11:51:30'),
(38, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-28 11:52:04'),
(39, 'Bhagyeshp', 'Login', 'User logged in successfully', '::1', '2026-01-28 11:53:49'),
(40, 'Bhagyeshp', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-IT-005_IT_Security_Policy.pdf)', '::1', '2026-01-28 11:54:00'),
(41, 'Bhagyeshp', 'Print SOP', 'Printed/Viewed SOP: it sop fomate new (SOP-IT-005_fmt_1769547357.pdf)', '::1', '2026-01-28 11:54:15'),
(42, 'Bhagyeshp', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-MF-002_1769545330.pdf)', '::1', '2026-01-28 11:54:30'),
(43, 'Bhagyeshp', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-MF-001_1769545228.pdf)', '::1', '2026-01-28 11:55:03'),
(44, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-28 11:55:26'),
(45, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-28 11:56:47'),
(46, 'test.admin', 'Failed Login', 'Invalid password', '::1', '2026-01-28 12:38:56'),
(47, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-28 12:39:04'),
(48, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-IT-005_IT_Security_Policy.pdf)', '::1', '2026-01-28 12:39:29'),
(49, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-IT-005_IT_Security_Policy.pdf)', '::1', '2026-01-28 12:41:56'),
(50, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-IT-005_IT_Security_Policy.pdf)', '::1', '2026-01-28 12:42:12'),
(51, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-IT-005_IT_Security_Policy.pdf)', '::1', '2026-01-28 12:42:35'),
(52, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-IT-005_IT_Security_Policy.pdf)', '::1', '2026-01-28 12:43:33'),
(53, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-IT-005_IT_Security_Policy.pdf)', '::1', '2026-01-28 12:43:54'),
(54, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-IT-005_IT_Security_Policy.pdf)', '::1', '2026-01-28 12:45:15'),
(55, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-28 12:47:32'),
(56, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-WH-003_Warehouse_Procedures.pdf)', '::1', '2026-01-28 12:47:46'),
(57, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-QA-010_QA_Handbook.pdf)', '::1', '2026-01-28 12:51:53'),
(58, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-28 12:52:55'),
(59, 'test.admin', 'Create User', 'Created user: Nirbhay (Nirbhay Mali) with role: admin', '::1', '2026-01-28 12:57:52'),
(60, 'test.admin', 'Reset Password', 'Reset password for user: Bhagyeshp', '::1', '2026-01-28 12:58:36'),
(61, 'test.admin', 'Reset Password', 'Reset password for user: test.user', '::1', '2026-01-28 12:58:45'),
(62, 'test.admin', 'Reset Password', 'Reset password for user: test.qa', '::1', '2026-01-28 12:58:53'),
(63, 'test.admin', 'Reset Password', 'Reset password for user: test.admin', '::1', '2026-01-28 12:58:59'),
(64, 'Bhagyeshp', 'Login', 'User logged in successfully', '::1', '2026-01-28 13:03:38'),
(65, 'Bhagyeshp', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-WH-003_Warehouse_Procedures.pdf)', '::1', '2026-01-28 13:04:00'),
(66, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-28 13:04:26'),
(67, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-IT-005_IT_Security_Policy.pdf)', '::1', '2026-01-28 13:04:37'),
(68, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-28 13:04:57'),
(69, 'Bhagyeshp', 'Login', 'User logged in successfully', '::1', '2026-01-28 13:05:27'),
(70, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-28 13:05:39'),
(71, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-28 13:07:14'),
(72, 'Bhagyeshp', 'Failed Login', 'Invalid password', '::1', '2026-01-28 13:07:45'),
(73, 'Bhagyeshp', 'Login', 'User logged in successfully', '::1', '2026-01-28 13:07:57'),
(74, 'Bhagyeshp', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-IT-005_IT_Security_Policy.pdf)', '::1', '2026-01-28 13:08:07'),
(75, 'Bhagyeshp', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-IT-005_IT_Security_Policy.pdf)', '::1', '2026-01-28 13:08:57'),
(76, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-28 13:09:18'),
(77, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-28 13:46:45'),
(78, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-28 16:20:07'),
(79, 'Bhagyeshp', 'Login', 'User logged in successfully', '::1', '2026-01-28 16:21:19'),
(80, 'Bhagyeshp', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-WH-003_Warehouse_Procedures.pdf)', '::1', '2026-01-28 16:22:28'),
(81, 'Bhagyeshp', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-WH-003_Warehouse_Procedures.pdf)', '::1', '2026-01-28 16:23:51'),
(82, 'Bhagyeshp', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-WH-003_Warehouse_Procedures.pdf)', '::1', '2026-01-28 16:24:00'),
(83, 'Bhagyeshp', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-WH-003_Warehouse_Procedures.pdf)', '::1', '2026-01-28 16:24:42'),
(84, 'test.user', 'Failed Login', 'Invalid password', '::1', '2026-01-28 16:26:08'),
(85, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-28 16:26:15'),
(86, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-28 16:27:28'),
(87, 'test.qa', 'Upload Format', 'Uploaded Format \'Confidential - 01\' for SOP: 1315', '::1', '2026-01-28 16:31:00'),
(88, 'test.qa', 'Upload SOP', 'Uploaded SOP: 1315 - Test', '::1', '2026-01-28 16:31:00'),
(89, 'test.qa', 'Upload Format', 'Uploaded Format \'Confidential - 01\' for SOP: 2222', '::1', '2026-01-28 16:32:51'),
(90, 'test.qa', 'Upload SOP', 'Uploaded SOP: 2222 - bhagyesh', '::1', '2026-01-28 16:32:51'),
(91, 'test.qa', 'Print SOP', 'Printed/Viewed SOP: Confidential - 01 (2222_fmt_1_1769598171.png)', '::1', '2026-01-28 16:36:30'),
(92, 'test.qa', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (2222_1769598171.png)', '::1', '2026-01-28 16:36:49'),
(93, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-28 16:39:48'),
(94, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-28 17:12:09'),
(95, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1315_1769598060.png)', '::1', '2026-01-28 17:12:17'),
(96, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-MF-002_1769545330.pdf)', '::1', '2026-01-28 17:12:23'),
(97, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-28 17:13:32'),
(98, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Test (1315_1769598060.png)', '::1', '2026-01-28 17:14:50'),
(99, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Confidential - 01 (1315_fmt_1_1769598060.png)', '::1', '2026-01-28 17:15:11'),
(100, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1315_1769598060.png)', '::1', '2026-01-28 17:15:15'),
(101, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1315_1769598060.png)', '::1', '2026-01-28 17:15:22'),
(102, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1315_1769598060.png)', '::1', '2026-01-28 17:15:24'),
(103, 'test.admin', 'Update SOP', 'Replaced Main File for SOP: 1315', '::1', '2026-01-28 17:15:44'),
(104, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Test (1315_1769600744.png)', '::1', '2026-01-28 17:15:51'),
(105, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1315_1769600744.png)', '::1', '2026-01-28 17:16:05'),
(106, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-29 09:23:23'),
(107, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1315_1769600744.png) | Reason: sg dsfg eragd  erg sag  rg arg  a awrg  arg ar g arw g r g', '::1', '2026-01-29 09:32:25'),
(108, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 09:38:41'),
(109, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 09:40:12'),
(110, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-29 09:40:33'),
(111, 'test.user', 'SOP Access Request', 'Requested SOP: Main SOP Document (1315_1769600744.png) | Reason: dfg refg aesrg awre  aw wre werg w rg w rg  w wr w er | Word Count: 15', '::1', '2026-01-29 09:42:49'),
(112, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1315_1769600744.png) | Reason: dfg refg aesrg awre  aw wre werg w rg w rg  w wr w er', '::1', '2026-01-29 09:42:49'),
(113, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 10:03:34'),
(114, 'test.admin', 'Create User', 'Created user: @Bhagyesh (Bhagyesh Patil) with role: admin', '::1', '2026-01-29 10:05:20'),
(115, '@Bhagyesh', 'Login', 'User logged in successfully', '::1', '2026-01-29 10:05:48'),
(116, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-29 10:40:05'),
(117, 'test.user', 'SOP Access Request', 'Requested SOP: Main SOP Document (SOP-MF-002_1769545330.pdf) | Reason: waef wef wef we r we we  wef wer wer wer we  wef we  we | Word Count: 15', '::1', '2026-01-29 10:42:05'),
(118, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-MF-002_1769545330.pdf) | Reason: waef wef wef we r we we  wef wer wer wer we  wef we  we', '::1', '2026-01-29 10:42:05'),
(119, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-MF-002_1769545330.pdf) | Reason: No reason provided', '::1', '2026-01-29 10:42:44'),
(120, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 10:43:08'),
(121, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-29 10:43:41'),
(122, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-29 10:44:57'),
(123, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 10:46:17'),
(124, 'test.admin', 'SOP Access Request', 'Requested SOP: Main SOP Document (SOP-MF-002_1769545330.pdf) | Reason: gf gf fg fg fg fg fg fg fg fg fg fg fg fg fg | Word Count: 15', '::1', '2026-01-29 11:12:36'),
(125, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (SOP-MF-002_1769545330.pdf) | Reason: gf gf fg fg fg fg fg fg fg fg fg fg fg fg fg', '::1', '2026-01-29 11:12:36'),
(126, 'test.admin', 'SOP Access Request', 'Requested SOP: Checklist (SOP-MF-002_fmt_1_1769545330.pdf) | Reason: gf gf gf gf g g g v v v v v v v v | Word Count: 15', '::1', '2026-01-29 11:18:19'),
(127, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Checklist (SOP-MF-002_fmt_1_1769545330.pdf) | Reason: gf gf gf gf g g g v v v v v v v v', '::1', '2026-01-29 11:18:19'),
(128, 'test.admin', 'Delete SOP', 'Deleted SOP: 1315 and its formats', '::1', '2026-01-29 11:24:18'),
(129, 'test.admin', 'Delete SOP', 'Deleted SOP: 2222 and its formats', '::1', '2026-01-29 11:24:20'),
(130, 'test.admin', 'Delete SOP', 'Deleted SOP: SOP-IT-005 and its formats', '::1', '2026-01-29 11:24:21'),
(131, 'test.admin', 'Delete SOP', 'Deleted SOP: SOP-MF-001 and its formats', '::1', '2026-01-29 11:24:21'),
(132, 'test.admin', 'Delete SOP', 'Deleted SOP: SOP-MF-002 and its formats', '::1', '2026-01-29 11:24:22'),
(133, 'test.admin', 'Delete SOP', 'Deleted SOP: SOP-QA-010 and its formats', '::1', '2026-01-29 11:24:23'),
(134, 'test.admin', 'Delete SOP', 'Deleted SOP: SOP-WH-003 and its formats', '::1', '2026-01-29 11:24:25'),
(135, 'test', 'Failed Login', 'User not found or inactive', '::1', '2026-01-29 11:34:59'),
(136, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-29 11:35:08'),
(137, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 11:45:30'),
(138, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-29 12:03:52'),
(139, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 12:26:24'),
(140, 'test.admin', 'Upload SOP', 'Uploaded SOP: 1008-23 - Traning of employees', '::1', '2026-01-29 12:30:01'),
(141, 'test.admin', 'Upload SOP', 'Uploaded SOP: 2266-11 - Issurance and usage of instrument log books and Register', '::1', '2026-01-29 12:33:59'),
(142, 'test.admin', 'Upload SOP', 'Uploaded SOP: 2281-13 - Review of Analytical data', '::1', '2026-01-29 12:35:04'),
(143, 'test.admin', 'Archive SOP', 'Archived old version of SOP: 2281-13', '::1', '2026-01-29 12:39:24'),
(144, 'test.admin', 'Upload Format', 'Uploaded Format \'Instrument log book\' for SOP: 2281-13', '::1', '2026-01-29 12:39:24'),
(145, 'test.admin', 'Upload Format', 'Uploaded Format \'Checklist for review of analytical and electronic data\' for SOP: 2281-13', '::1', '2026-01-29 12:39:24'),
(146, 'test.admin', 'Upload SOP', 'Uploaded SOP: 2281-13 - Review of Analytical data (New Version)', '::1', '2026-01-29 12:39:24'),
(147, 'test.admin', 'Upload SOP', 'Uploaded SOP: F4-1008-06 - Record of Training Attendance and Evolution', '::1', '2026-01-29 12:41:19'),
(148, 'test.admin', 'SOP Access Request', 'Requested SOP: Main SOP Document (2266-11_1769670239.pdf) | Reason: f gsg sdg s sdgdsg sdg sdg sdg sdgsd sdg sdg sdg sdg sdg sdg sd | Word Count: 16', '::1', '2026-01-29 12:41:54'),
(149, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (2266-11_1769670239.pdf) | Reason: f gsg sdg s sdgdsg sdg sdg sdg sdgsd sdg sdg sdg sdg sdg sdg sd', '::1', '2026-01-29 12:41:54'),
(150, 'test.admin', 'SOP Access Request', 'Requested SOP: Main SOP Document (2266-11_1769670239.pdf) | Reason: df sdfg dsfg dsfg dfg df gdf g dfg dfg dfsdf gdf g dfg dfg | Word Count: 15', '::1', '2026-01-29 12:43:13'),
(151, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (2266-11_1769670239.pdf) | Reason: df sdfg dsfg dsfg dfg df gdf g dfg dfg dfsdf gdf g dfg dfg', '::1', '2026-01-29 12:43:13'),
(152, 'test.admin', 'SOP Access Request', 'Requested SOP: Main SOP Document (2266-11_1769670239.pdf) | Reason: df sdfg dsfg dsfg dfg df gdf g dfg dfg dfsdf gdf g dfg dfg | Word Count: 15', '::1', '2026-01-29 12:46:03'),
(153, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (2266-11_1769670239.pdf) | Reason: df sdfg dsfg dsfg dfg df gdf g dfg dfg dfsdf gdf g dfg dfg', '::1', '2026-01-29 12:46:03'),
(154, 'test.admin', 'SOP Access Request', 'Requested SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: dfg dsf gdsf gdf gdf g dfg dsfg dsfg afdsg dasf gdf g dfg dsfg dfsg | Word Count: 16', '::1', '2026-01-29 12:46:16'),
(155, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: dfg dsf gdsf gdf gdf g dfg dsfg dsfg afdsg dasf gdf g dfg dsfg dfsg', '::1', '2026-01-29 12:46:16'),
(156, 'test.admin', 'SOP Access Request', 'Requested SOP: Checklist for review of analytical and electronic data (2281-13_fmt_2_1769670564.pdf) | Reason: dfgdf g dfg sdfg dsf g sdfg dfg df g dfg dsf gsdf g d | Word Count: 15', '::1', '2026-01-29 12:46:30'),
(157, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Checklist for review of analytical and electronic data (2281-13_fmt_2_1769670564.pdf) | Reason: dfgdf g dfg sdfg dsf g sdfg dfg df g dfg dsf gsdf g d', '::1', '2026-01-29 12:46:30'),
(158, 'test.admin', 'SOP Access Request', 'Requested SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: fdgh fdg hf dgh fdgh fdg hdf gh fgdh fgd hfd gh fdgh fdg hfd gh | Word Count: 16', '::1', '2026-01-29 12:46:44'),
(159, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: fdgh fdg hf dgh fdgh fdg hdf gh fgdh fgd hfd gh fdgh fdg hfd gh', '::1', '2026-01-29 12:46:44'),
(160, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 12:52:01'),
(161, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Review of Analytical data (2281-13_1769670304.pdf) | Reason: No reason provided', '::1', '2026-01-29 12:53:59'),
(162, '@Bhagyesh', 'Login', 'User logged in successfully', '::1', '2026-01-29 12:54:42'),
(163, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-29 12:55:03'),
(164, 'test.qa', 'Print SOP', 'Printed/Viewed SOP: Review of Analytical data (2281-13_1769670304.pdf) | Reason: No reason provided', '::1', '2026-01-29 12:57:14'),
(165, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-29 12:57:38'),
(166, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-29 13:00:41'),
(167, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 13:01:21'),
(168, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-29 14:15:20'),
(169, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 14:16:51'),
(170, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-29 14:18:23'),
(171, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-29 14:45:57'),
(172, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 14:46:20'),
(173, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Review of Analytical data (2281-13_1769670304.pdf) | Reason: No reason provided', '::1', '2026-01-29 14:46:30'),
(174, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Review of Analytical data (2281-13_1769670304.pdf) | Reason: No reason provided', '::1', '2026-01-29 14:48:18'),
(175, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Review of Analytical data (2281-13_1769670304.pdf) | Reason: No reason provided', '::1', '2026-01-29 14:48:59'),
(176, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Review of Analytical data (2281-13_1769670304.pdf) | Reason: No reason provided', '::1', '2026-01-29 14:49:00'),
(177, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Review of Analytical data (2281-13_1769670304.pdf) | Reason: No reason provided', '::1', '2026-01-29 14:50:40'),
(178, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Review of Analytical data (2281-13_1769670304.pdf) | Reason: No reason provided', '::1', '2026-01-29 14:50:41'),
(179, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Review of Analytical data (2281-13_1769670304.pdf) | Reason: No reason provided', '::1', '2026-01-29 14:51:02'),
(180, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Review of Analytical data (2281-13_1769670304.pdf) | Reason: No reason provided', '::1', '2026-01-29 14:51:28'),
(181, 'test.admin', 'SOP Access Request', 'Requested SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: dfg dsfg dsf gd sfg dsfg dsf gdf g dfg dfg df gd fsg sdf | Word Count: 15', '::1', '2026-01-29 14:53:41'),
(182, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: dfg dsfg dsf gd sfg dsfg dsf gdf g dfg dfg df gd fsg sdf', '::1', '2026-01-29 14:53:41'),
(183, 'test.admin', 'SOP Access Request', 'Requested SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: dfg dfg dsf gsdf g dsfg df gdsf g dfg dsf g sdfg dfsg dsfg dsf g | Word Count: 17', '::1', '2026-01-29 14:54:02'),
(184, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: dfg dfg dsf gsdf g dsfg df gdsf g dfg dsf g sdfg dfsg dsfg dsf g', '::1', '2026-01-29 14:54:02'),
(185, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Review of Analytical data (2281-13_1769670304.pdf) | Reason: No reason provided', '::1', '2026-01-29 14:54:13'),
(186, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-29 15:33:41'),
(187, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 15:38:49'),
(188, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-29 15:40:22'),
(189, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-29 15:42:07'),
(190, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 15:45:35'),
(191, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 15:51:14'),
(192, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-29 15:51:22'),
(193, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-29 15:54:10'),
(194, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-29 15:56:43'),
(195, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (2281-13_1769670564.pdf) | Reason: No reason provided', '::1', '2026-01-29 16:06:41'),
(196, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: No reason provided', '::1', '2026-01-29 16:06:50'),
(197, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (2281-13_1769670564.pdf) | Reason: No reason provided', '::1', '2026-01-29 16:07:05'),
(198, 'test.user', 'SOP Access Request', 'Requested access to: Main SOP Document with reason: dfg df g df  dsfg d fg df g dfg fd g fdg fdg  df', '::1', '2026-01-29 16:16:20'),
(199, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (F4-1008-06_1769670679.pdf) | Reason: dfg df g df  dsfg d fg df g dfg fd g fdg fdg  df', '::1', '2026-01-29 16:16:20'),
(200, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 16:18:00'),
(201, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-29 16:21:41'),
(202, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 16:22:15'),
(203, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-29 16:27:11'),
(204, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-29 16:29:00'),
(205, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-29 16:29:10'),
(206, 'test.user', 'SOP Access Request', 'Requested access to: Instrument log book with reason: kj nkj nij n jn jn ojk gfh fg hfg hfg h fgh fgh fg hfg', '::1', '2026-01-29 16:30:20'),
(207, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: kj nkj nij n jn jn ojk gfh fg hfg hfg h fgh fgh fg hfg', '::1', '2026-01-29 16:30:20'),
(208, 'test.user', 'SOP Access Request', 'Requested access to: Main SOP Document with reason: dxfgb dfxb dsf gdfs g dsfg dsfg dsf g dfg dfg sdf g dfsg df g', '::1', '2026-01-29 16:33:20'),
(209, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (2266-11_1769670239.pdf) | Reason: dxfgb dfxb dsf gdfs g dsfg dsfg dsf g dfg dfg sdf g dfsg df g', '::1', '2026-01-29 16:33:20'),
(210, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 16:33:37'),
(211, 'test.qa', 'Failed Login', 'Invalid password', '::1', '2026-01-29 16:36:44'),
(212, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-29 16:36:52'),
(213, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 16:37:40'),
(214, 'test.qa', 'Failed Login', 'Invalid password', '::1', '2026-01-29 18:02:20'),
(215, 'test.qa', 'Failed Login', 'Invalid password', '::1', '2026-01-29 18:02:42'),
(216, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-29 18:03:07'),
(217, '@Bhagyesh', 'Login', 'User logged in successfully', '::1', '2026-01-30 10:07:55'),
(218, '@Bhagyesh', 'Export Print Logs', 'Exported print logs to Excel', '::1', '2026-01-30 10:08:36'),
(219, '@Bhagyesh', 'Export Archived SOPs', 'Exported archived SOPs to Excel', '::1', '2026-01-30 10:15:27'),
(220, '@Bhagyesh', 'Export Audit Trail', 'Exported audit trail to Excel', '::1', '2026-01-30 10:25:24'),
(221, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-30 10:26:16'),
(222, 'test.user', 'SOP Access Request', 'Requested access to: Instrument log book with reason: sgfdh sdfgh sdfgh sdfgh sdfgh sdfhg', '::1', '2026-01-30 10:27:57'),
(223, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: sgfdh sdfgh sdfgh sdfgh sdfgh sdfhg', '::1', '2026-01-30 10:27:57'),
(224, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-30 10:34:46'),
(225, 'test.qa', 'Print SOP', 'Printed/Viewed SOP: Review of Analytical data (2281-13_1769670304.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:35:49'),
(226, 'test.qa', 'Print SOP', 'Printed/Viewed SOP: Review of Analytical data (2281-13_1769670304.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:37:21'),
(227, '@Bhagyesh', 'Login', 'User logged in successfully', '::1', '2026-01-30 10:38:15'),
(228, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:48:27'),
(229, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (2281-13_1769670304.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:49:18'),
(230, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (2281-13_1769670304.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:49:29'),
(231, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (2281-13_1769670304.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:49:33'),
(232, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:49:40'),
(233, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:50:39'),
(234, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:50:47'),
(235, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (F4-1008-06_1769670679.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:51:20'),
(236, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:51:27'),
(237, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:51:55'),
(238, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:52:10'),
(239, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:52:11'),
(240, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:53:22'),
(241, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:53:52'),
(242, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:53:53'),
(243, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:53:53'),
(244, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:53:53'),
(245, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:53:53'),
(246, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:53:53'),
(247, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:54:38'),
(248, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:54:54'),
(249, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:55:44'),
(250, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:56:48'),
(251, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:57:28'),
(252, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:57:46'),
(253, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:58:22'),
(254, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:58:48'),
(255, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (1008-23_1769670001.pdf) | Reason: No reason provided', '::1', '2026-01-30 10:59:02'),
(256, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: No reason provided', '::1', '2026-01-30 11:04:50'),
(257, '@Bhagyesh', 'Print SOP', 'Printed/Viewed SOP: Checklist for review of analytical and electronic data (2281-13_fmt_2_1769670564.pdf) | Reason: No reason provided', '::1', '2026-01-30 11:08:31'),
(258, 'test.qa', 'Failed Login', 'Invalid password', '::1', '2026-01-30 11:13:03'),
(259, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-30 11:13:18'),
(260, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-30 12:18:33'),
(261, 'test.user', 'SOP Access Request', 'Requested access to: Instrument log book with reason: rtsh  rth gfh gfdh fgh fgh', '::1', '2026-01-30 12:18:53'),
(262, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: rtsh  rth gfh gfdh fgh fgh', '::1', '2026-01-30 12:18:53'),
(263, 'test.user', 'SOP Access Request', 'Requested access to: Main SOP Document with reason: hj dfgj dfg hjdfg hjfdg hj fdgjh', '::1', '2026-01-30 12:32:25'),
(264, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (2266-11_1769670239.pdf) | Reason: hj dfgj dfg hjdfg hjfdg hj fdgjh', '::1', '2026-01-30 12:32:25'),
(265, 'test.user', 'SOP Access Request', 'Requested access to: Instrument log book with reason: gsfdh fgd h fdgh fgdh fgd h', '::1', '2026-01-30 12:33:00'),
(266, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: gsfdh fgd h fdgh fgdh fgd h', '::1', '2026-01-30 12:33:00'),
(267, 'test.user', 'SOP Access Request', 'Requested access to: Checklist for review of analytical and electronic data with reason: gfh fdg hf gh gfh gf hgf', '::1', '2026-01-30 12:33:21'),
(268, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Checklist for review of analytical and electronic data (2281-13_fmt_2_1769670564.pdf) | Reason: gfh fdg hf gh gfh gf hgf', '::1', '2026-01-30 12:33:21'),
(269, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Checklist for review of analytical and electronic data (2281-13_fmt_2_1769670564.pdf) | Reason: No reason provided', '::1', '2026-01-30 12:40:19'),
(270, 'test.user', 'SOP Access Request', 'Requested access to: Instrument log book with reason: f gh fdgh fdg hfdg h dfgh', '::1', '2026-01-30 12:40:34'),
(271, 'test.user', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: f gh fdgh fdg hfdg h dfgh', '::1', '2026-01-30 12:40:34'),
(272, 'Bhagyeshp', 'Failed Login', 'User not found or inactive', '::1', '2026-01-30 12:56:21'),
(273, '@Bhagyesh', 'Login', 'User logged in successfully', '::1', '2026-01-30 12:56:33'),
(274, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-30 12:57:00'),
(275, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-30 13:01:05'),
(276, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: No reason provided', '::1', '2026-01-30 13:02:34'),
(277, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: No reason provided', '::1', '2026-01-30 13:07:06'),
(278, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: No reason provided', '::1', '2026-01-30 13:13:11'),
(279, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: No reason provided', '::1', '2026-01-30 13:13:31'),
(280, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: No reason provided', '::1', '2026-01-30 13:13:41'),
(281, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: No reason provided', '::1', '2026-01-30 13:13:53'),
(282, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: No reason provided', '::1', '2026-01-30 13:14:05'),
(283, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: No reason provided', '::1', '2026-01-30 13:16:30'),
(284, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Instrument log book (2281-13_fmt_1_1769670564.pdf) | Reason: No reason provided', '::1', '2026-01-30 13:16:37'),
(285, 'test.admin', 'Export Archived SOPs', 'Exported archived SOPs to Excel', '::1', '2026-01-30 14:04:43'),
(286, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-30 14:08:36'),
(287, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-30 14:34:31'),
(288, 'test.admin', 'Upload Format', 'Uploaded Format \'Confidential - 01\' for SOP: 001', '::1', '2026-01-30 14:39:01'),
(289, 'test.admin', 'Upload SOP', 'Uploaded SOP: 001 - vfgh', '::1', '2026-01-30 14:39:01'),
(290, 'test.admin', 'Delete SOP', 'Deleted SOP: 001 and its formats', '::1', '2026-01-30 14:48:29'),
(291, 'test.admin', 'Delete SOP', 'Deleted SOP: 1008-23 and its formats', '::1', '2026-01-30 14:48:31'),
(292, 'test.admin', 'Delete SOP', 'Deleted SOP: 2266-11 and its formats', '::1', '2026-01-30 14:48:33'),
(293, 'test.admin', 'Delete SOP', 'Deleted SOP: 2281-13 and its formats', '::1', '2026-01-30 14:48:34'),
(294, 'test.admin', 'Delete SOP', 'Deleted SOP: 2281-13 and its formats', '::1', '2026-01-30 14:48:35'),
(295, 'test.admin', 'Delete SOP', 'Deleted SOP: F4-1008-06 and its formats', '::1', '2026-01-30 14:48:36'),
(296, 'test.admin', 'Upload Format', 'Uploaded Format \'Confidential - 01\' for SOP: 2281', '::1', '2026-01-30 14:49:30'),
(297, 'test.admin', 'Upload SOP', 'Uploaded SOP: 2281 - Sumit (formats only)', '::1', '2026-01-30 14:49:30'),
(298, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document () | Reason: No reason provided', '::1', '2026-01-30 14:49:40'),
(299, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Confidential - 01 (2281_fmt_1_1769764770.pdf) | Reason: No reason provided', '::1', '2026-01-30 14:49:47'),
(300, 'test.admin', 'Delete SOP', 'Deleted SOP: 2281 and its formats', '::1', '2026-01-30 14:52:16'),
(301, 'test.admin', 'Upload Format', 'Uploaded Format \'Confidential - 01\' for SOP: 001', '::1', '2026-01-30 14:52:42'),
(302, 'test.admin', 'Upload SOP', 'Uploaded SOP: 001 - Sumit (formats only)', '::1', '2026-01-30 14:52:42'),
(303, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Confidential - 01 (001_fmt_1_1769764962.pdf) | Reason: No reason provided', '::1', '2026-01-30 14:53:14'),
(304, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Confidential - 01 (001_fmt_1_1769764962.pdf) | Reason: No reason provided', '::1', '2026-01-30 14:53:36'),
(305, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Confidential - 01 (001_fmt_1_1769764962.pdf) | Reason: No reason provided', '::1', '2026-01-30 14:54:08'),
(306, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Sumit () | Reason: No reason provided', '::1', '2026-01-30 14:54:37'),
(307, 'test.admin', 'Update SOP', 'Replaced Main File for SOP: 001', '::1', '2026-01-30 14:54:50'),
(308, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Sumit (001_1769765090.pdf) | Reason: No reason provided', '::1', '2026-01-30 14:55:35'),
(309, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (001_1769765090.pdf) | Reason: No reason provided', '::1', '2026-01-30 14:55:47'),
(310, 'test.admin', 'Add Format', 'Added format \'Confidential - 01\' to SOP: 001', '::1', '2026-01-30 14:56:27'),
(311, 'test.admin', 'Delete Format', 'Deleted format from SOP: 001', '::1', '2026-01-30 15:09:48'),
(312, 'test.admin', 'Replace Format', 'Replaced format \'Confidential - 01\' in SOP: 001', '::1', '2026-01-30 15:10:00'),
(313, 'test.admin', 'Delete Format', 'Deleted format from SOP: 001', '::1', '2026-01-30 15:17:19'),
(314, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Main SOP Document (001_1769765090.pdf) | Reason: No reason provided', '::1', '2026-01-30 15:18:49'),
(315, 'test.admin', 'Replace Format', 'Replaced format \'Confidential - 01\' in SOP: 001 (v2)', '::1', '2026-01-30 15:20:19'),
(316, 'test.admin', 'Replace Format', 'Replaced format \'Confidential - 01\' in SOP: 001 (v3)', '::1', '2026-01-30 15:36:15'),
(317, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Confidential - 01 (001_fmt_1769766619.pdf) | Reason: No reason provided', '::1', '2026-01-30 15:36:19'),
(318, 'test.admin', 'Delete SOP', 'Deleted SOP: 001 and its formats', '::1', '2026-01-30 15:36:53'),
(319, 'test.admin', 'Upload Format', 'Uploaded Format \'Confidential - 01\' for SOP: 001', '::1', '2026-01-30 15:37:26'),
(320, 'test.admin', 'Upload SOP', 'Uploaded SOP: 001 - Sumit (formats only)', '::1', '2026-01-30 15:37:26'),
(321, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Confidential - 01 (001_fmt_1_1769767646.pdf) | Reason: No reason provided', '::1', '2026-01-30 15:37:31'),
(322, 'test.admin', 'Replace Format', 'Replaced format \'Confidential - 01\' in SOP: 001 (v2)', '::1', '2026-01-30 15:38:18'),
(323, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Confidential - 01 (001_fmt_1_1769767646.pdf) | Reason: No reason provided', '::1', '2026-01-30 15:38:21'),
(324, 'test.admin', 'Replace Format', 'Replaced format \'Confidential - 01\' in SOP: 001 (v3)', '::1', '2026-01-30 15:42:15'),
(325, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Confidential - 01 (001_fmt_1_1769767646.pdf) | Reason: No reason provided', '::1', '2026-01-30 15:43:09'),
(326, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Confidential - 01 (001_fmt_1769767698.pdf) | Reason: No reason provided', '::1', '2026-01-30 15:43:13'),
(327, 'test.admin', 'Print SOP', 'Printed/Viewed SOP: Confidential - 01 (001_fmt_1769767935.pdf) | Reason: No reason provided', '::1', '2026-01-30 15:43:19'),
(328, 'test.admin', 'Add Format', 'Added format \'Confidential - 02\' to SOP: 001', '::1', '2026-01-30 16:47:04'),
(329, 'test.admin', 'Archive SOP', 'Archived old version of SOP: 001', '::1', '2026-01-30 16:48:19'),
(330, 'test.admin', 'Upload Format', 'Uploaded Format \'Confidential - 01\' for SOP: 001', '::1', '2026-01-30 16:48:19'),
(331, 'test.admin', 'Upload SOP', 'Uploaded SOP: 001 - Sumit (formats only) (New Version)', '::1', '2026-01-30 16:48:19'),
(332, 'test.qa', 'Login', 'User logged in successfully', '::1', '2026-01-30 16:52:15'),
(333, 'test.qa', 'Replace Format', 'Replaced format \'Confidential - 01\' in SOP: 001 (v4)', '::1', '2026-01-30 16:52:33'),
(334, 'test.qa', 'Add Format', 'Added format \'Confidential\' to SOP: 001', '::1', '2026-01-30 16:54:56'),
(335, 'test.qa', 'Replace Format', 'Replaced format \'Confidential\' in SOP: 001 (v2)', '::1', '2026-01-30 16:55:10'),
(336, 'test.qa', 'Delete SOP', 'Deleted SOP: 001 and its formats', '::1', '2026-01-30 16:58:15'),
(337, 'test.qa', 'Delete SOP', 'Deleted SOP: 001 and its formats', '::1', '2026-01-30 16:58:17'),
(338, 'test.qa', 'Upload Format', 'Uploaded Format \'Confidential\' for SOP: 001', '::1', '2026-01-30 16:58:40'),
(339, 'test.qa', 'Upload SOP', 'Uploaded SOP: 001 - ss (formats only)', '::1', '2026-01-30 16:58:40'),
(340, 'test.qa', 'Print SOP', 'Printed/Viewed SOP: Confidential (001_fmt_1_1769772520.pdf) | Reason: No reason provided', '::1', '2026-01-30 16:58:47'),
(341, 'test.qa', 'Replace Format', 'Replaced format \'Confidential\' in SOP: 001 (v2)', '::1', '2026-01-30 16:59:18'),
(342, 'test.qa', 'Print SOP', 'Printed/Viewed SOP: Confidential (001_fmt_1769772558.pdf) | Reason: No reason provided', '::1', '2026-01-30 17:01:37'),
(343, 'test.qa', 'Add Format', 'Added format \'Confidential - 01\' to SOP: 001', '::1', '2026-01-30 17:01:54'),
(344, 'test.qa', 'Add Format', 'Added format \'Confidential - 02\' to SOP: 001', '::1', '2026-01-30 17:02:04'),
(345, 'test.qa', 'Print SOP', 'Printed/Viewed SOP: Confidential (001_fmt_1769772558.pdf) | Reason: No reason provided', '::1', '2026-01-30 17:02:12'),
(346, 'test.qa', 'Print SOP', 'Printed/Viewed SOP: Confidential - 01 (001_fmt_1769772714.pdf) | Reason: No reason provided', '::1', '2026-01-30 17:14:38'),
(347, 'test.qa', 'Print SOP', 'Printed/Viewed SOP: Confidential - 02 (001_fmt_1769772724.pdf) | Reason: No reason provided', '::1', '2026-01-30 17:14:46'),
(348, 'test.qa', 'Print SOP', 'Printed/Viewed SOP: Confidential (001_fmt_1769772558.pdf) | Reason: No reason provided', '::1', '2026-01-30 17:14:51'),
(349, 'test.user', 'Login', 'User logged in successfully', '::1', '2026-01-30 17:15:30'),
(350, 'test.admin', 'Login', 'User logged in successfully', '::1', '2026-01-30 17:17:16'),
(351, 'test.admin', 'Export Print Logs', 'Exported print logs to Excel', '::1', '2026-01-30 17:26:03'),
(352, 'test.admin', 'Export Print Logs', 'Exported print logs to Excel', '::1', '2026-01-30 17:28:29'),
(353, 'test.admin', 'Export Print Logs', 'Exported print logs to Excel', '::1', '2026-01-30 17:28:39'),
(354, 'test.admin', 'Export Print Logs', 'Exported print logs to Excel', '::1', '2026-01-30 17:28:44');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `code`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'IT', 'Information Technology', NULL, 1, '2026-01-27 18:23:08', '2026-01-27 18:23:08'),
(2, 'Q.A.', 'Quality Assurance', NULL, 1, '2026-01-27 18:23:08', '2026-01-27 18:23:08'),
(3, 'Q.C.', 'Quality Control', NULL, 1, '2026-01-27 18:23:08', '2026-01-27 18:23:08'),
(4, 'P&G', 'Production & General', NULL, 1, '2026-01-27 18:23:09', '2026-01-27 18:23:09'),
(5, 'RA', 'Regulatory Affairs', NULL, 1, '2026-01-27 18:23:09', '2026-01-27 18:23:09'),
(6, 'WAREHOUSE', 'Warehouse Operations', NULL, 1, '2026-01-27 18:23:09', '2026-01-27 18:23:09');

-- --------------------------------------------------------

--
-- Table structure for table `fileup`
--

CREATE TABLE `fileup` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `sop_number` varchar(255) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `version` varchar(50) DEFAULT '1.0',
  `uploaded_by` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `status` enum('active','archived','draft') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `fileup`
--

INSERT INTO `fileup` (`id`, `title`, `sop_number`, `department_id`, `image`, `version`, `uploaded_by`, `file_size`, `status`, `created_at`) VALUES
(18, 'ss', '001', 4, '', '1.0', NULL, NULL, 'active', '2026-01-30 11:28:40');

-- --------------------------------------------------------

--
-- Table structure for table `knowledge_tips`
--

CREATE TABLE `knowledge_tips` (
  `id` int(11) NOT NULL,
  `category` enum('GMP','21CFR','SOP','Change Control','Incident','General') NOT NULL DEFAULT 'General',
  `tip_content` text NOT NULL,
  `added_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `knowledge_tips`
--

INSERT INTO `knowledge_tips` (`id`, `category`, `tip_content`, `added_by`, `created_at`) VALUES
(1, 'GMP', 'Good Manufacturing Practice (GMP) is a system for ensuring that products are consistently produced and controlled according to quality standards.', NULL, '2026-01-27 19:11:32'),
(2, 'GMP', 'Documentation is key in GMP: If it’s not written down, it didn’t happen.', NULL, '2026-01-27 19:11:32'),
(3, 'GMP', 'Cleanliness is next to Godliness in a GMP environment. Always maintain proper hygiene.', NULL, '2026-01-27 19:11:32'),
(4, 'GMP', 'Cross-contamination must be prevented at all costs. Use dedicated equipment where necessary.', NULL, '2026-01-27 19:11:32'),
(5, 'GMP', 'Process validation provides documented evidence that a process consistently produces a result meeting predetermined specifications.', NULL, '2026-01-27 19:11:32'),
(6, 'GMP', 'Calibrate equipment regularly to ensure accuracy and reliability of measurements.', NULL, '2026-01-27 19:11:32'),
(7, 'GMP', 'Raw materials must be sourced from approved vendors and tested before use.', NULL, '2026-01-27 19:11:32'),
(8, 'GMP', 'Personnel training is a continuous process in GMP. Stay updated.', NULL, '2026-01-27 19:11:32'),
(9, 'GMP', 'Line clearance is mandatory before starting any new packaging operation to avoid mix-ups.', NULL, '2026-01-27 19:11:32'),
(10, 'GMP', 'Labels must be controlled and accounted for strictly to prevent mislabelling.', NULL, '2026-01-27 19:11:32'),
(11, '21CFR', '21 CFR Part 11 regulates Electronic Records and Electronic Signatures (ERES).', NULL, '2026-01-27 19:11:32'),
(12, '21CFR', 'Under 21 CFR Part 211, production and control records must be reviewed and approved by the Quality Control Unit.', NULL, '2026-01-27 19:11:32'),
(13, '21CFR', 'Data integrity is the cornerstone of 21 CFR compliance. ALCOA+ principles must be followed.', NULL, '2026-01-27 19:11:32'),
(14, '21CFR', 'ALCOA stands for Attributable, Legible, Contemporaneous, Original, and Accurate.', NULL, '2026-01-27 19:11:32'),
(15, '21CFR', 'Audit trails must be secure, computer-generated, and time-stamped to record the date and time of operator entries.', NULL, '2026-01-27 19:11:32'),
(16, '21CFR', 'Electronic signatures must be unique to one individual and cannot be reassigned.', NULL, '2026-01-27 19:11:32'),
(17, '21CFR', 'System access must be restricted to authorized users only (Security controls).', NULL, '2026-01-27 19:11:32'),
(18, '21CFR', 'Periodic reviews of audit trails are essential to detect unauthorized changes or data manipulation.', NULL, '2026-01-27 19:11:32'),
(19, '21CFR', 'Validation of computerized systems is required to ensure they perform as intended.', NULL, '2026-01-27 19:11:32'),
(20, '21CFR', 'Backup and recovery procedures must be in place to protect electronic records.', NULL, '2026-01-27 19:11:32'),
(21, 'SOP', 'Standard Operating Procedures (SOPs) ensure consistency and quality in operations.', NULL, '2026-01-27 19:11:32'),
(22, 'SOP', 'SOPs must be written in clear, concise language that is easily understood by the user.', NULL, '2026-01-27 19:11:32'),
(23, 'SOP', 'Regular review of SOPs ensures they remain current and effective.', NULL, '2026-01-27 19:11:32'),
(24, 'SOP', 'Deviations from SOPs must be documented, investigated, and justified.', NULL, '2026-01-27 19:11:32'),
(25, 'SOP', 'Controlled copies of SOPs must be distributed to relevant departments.', NULL, '2026-01-27 19:11:32'),
(26, 'SOP', 'Obsolete SOPs must be withdrawn immediately to prevent unintended use.', NULL, '2026-01-27 19:11:32'),
(27, 'SOP', 'SOP training must be documented for all affected personnel before implementation.', NULL, '2026-01-27 19:11:32'),
(28, 'SOP', 'SOPs should include a flowchart for complex processes to aid understanding.', NULL, '2026-01-27 19:11:32'),
(29, 'SOP', 'Effective SOPs minimize variation and reduce the risk of errors.', NULL, '2026-01-27 19:11:32'),
(30, 'SOP', 'Always follow the current version of the SOP. Check the effective date.', NULL, '2026-01-27 19:11:32'),
(31, 'Change Control', 'Change Control is a formal system to evaluate and manage proposed changes.', NULL, '2026-01-27 19:11:32'),
(32, 'Change Control', 'Proposed changes must be reviewed for their impact on product quality and regulatory compliance.', NULL, '2026-01-27 19:11:32'),
(33, 'Change Control', 'No change should be implemented without prior approval from the Quality Unit.', NULL, '2026-01-27 19:11:32'),
(34, 'Change Control', 'Risk assessment is a critical part of the change control process.', NULL, '2026-01-27 19:11:32'),
(35, 'Change Control', 'Post-implementation review ensures the change achieved its intended outcome.', NULL, '2026-01-27 19:11:32'),
(36, 'Change Control', 'Regulatory authorities must be notified of significant changes as per guidelines.', NULL, '2026-01-27 19:11:32'),
(37, 'Change Control', 'Changes to equipment, facilities, processes, or systems all require Change Control.', NULL, '2026-01-27 19:11:32'),
(38, 'Change Control', 'Temporary changes must have a defined expiration date.', NULL, '2026-01-27 19:11:32'),
(39, 'Change Control', 'Documentation of the change rationale is crucial for future reference.', NULL, '2026-01-27 19:11:32'),
(40, 'Change Control', 'Uncontrolled changes are a major cause of quality issues and non-compliance.', NULL, '2026-01-27 19:11:32'),
(41, 'Incident', 'An incident is any unplanned event that may affect product quality or safety.', NULL, '2026-01-27 19:11:32'),
(42, 'Incident', 'Immediate reporting of incidents is vital for timely investigation.', NULL, '2026-01-27 19:11:32'),
(43, 'Incident', 'Root Cause Analysis (RCA) must be performed to identify the underlying cause of an incident.', NULL, '2026-01-27 19:11:32'),
(44, 'Incident', 'CAPA (Corrective and Preventive Action) is the outcome of a successful incident investigation.', NULL, '2026-01-27 19:11:32'),
(45, 'Incident', 'Corrective action eliminates the cause of a detected non-conformity.', NULL, '2026-01-27 19:11:32'),
(46, 'Incident', 'Preventive action eliminates the cause of a potential non-conformity.', NULL, '2026-01-27 19:11:32'),
(47, 'Incident', 'Effectiveness checks determine if the CAPA successfully prevented recurrence.', NULL, '2026-01-27 19:11:32'),
(48, 'Incident', 'Human error is often a symptom, not the root cause. Dig deeper (5 Whys).', NULL, '2026-01-27 19:11:32'),
(49, 'Incident', 'Trending of incidents helps identify systemic issues.', NULL, '2026-01-27 19:11:32'),
(50, 'Incident', 'Failure to investigate incidents can lead to regulatory observations (483s).', NULL, '2026-01-27 19:11:32'),
(51, 'GMP', 'Browser Test Tip', 5, '2026-01-27 19:16:22');

-- --------------------------------------------------------

--
-- Table structure for table `login_detail`
--

CREATE TABLE `login_detail` (
  `name` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `userid` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `empcode` int(11) NOT NULL,
  `password` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sop_access_requests`
--

CREATE TABLE `sop_access_requests` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `sop_id` int(11) DEFAULT NULL,
  `sop_title` varchar(255) DEFAULT NULL,
  `sop_number` varchar(50) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `reason` longtext NOT NULL,
  `word_count` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sop_access_requests`
--

INSERT INTO `sop_access_requests` (`id`, `user_id`, `sop_id`, `sop_title`, `sop_number`, `file_name`, `reason`, `word_count`, `ip_address`, `requested_at`) VALUES
(1, 'test.user', NULL, 'Main SOP Document', '1315', '1315_1769600744.png', 'dfg refg aesrg awre  aw wre werg w rg w rg  w wr w er', 15, '::1', '2026-01-29 04:12:49'),
(2, 'test.user', NULL, 'Main SOP Document', 'SOP-MF-002', 'SOP-MF-002_1769545330.pdf', 'waef wef wef we r we we  wef wer wer wer we  wef we  we', 15, '::1', '2026-01-29 05:12:05'),
(3, 'test.admin', NULL, 'Main SOP Document', 'SOP-MF-002', 'SOP-MF-002_1769545330.pdf', 'gf gf fg fg fg fg fg fg fg fg fg fg fg fg fg', 15, '::1', '2026-01-29 05:42:36'),
(4, 'test.admin', NULL, 'Checklist', 'SOP-MF-002', 'SOP-MF-002_fmt_1_1769545330.pdf', 'gf gf gf gf g g g v v v v v v v v', 15, '::1', '2026-01-29 05:48:19'),
(5, 'test.admin', NULL, 'Main SOP Document', '2266-11', '2266-11_1769670239.pdf', 'f gsg sdg s sdgdsg sdg sdg sdg sdgsd sdg sdg sdg sdg sdg sdg sd', 16, '::1', '2026-01-29 07:11:54'),
(6, 'test.admin', NULL, 'Main SOP Document', '2266-11', '2266-11_1769670239.pdf', 'df sdfg dsfg dsfg dfg df gdf g dfg dfg dfsdf gdf g dfg dfg', 15, '::1', '2026-01-29 07:13:13'),
(7, 'test.admin', NULL, 'Main SOP Document', '2266-11', '2266-11_1769670239.pdf', 'df sdfg dsfg dsfg dfg df gdf g dfg dfg dfsdf gdf g dfg dfg', 15, '::1', '2026-01-29 07:16:03'),
(8, 'test.admin', NULL, 'Instrument log book', '2281-13', '2281-13_fmt_1_1769670564.pdf', 'dfg dsf gdsf gdf gdf g dfg dsfg dsfg afdsg dasf gdf g dfg dsfg dfsg', 16, '::1', '2026-01-29 07:16:16'),
(9, 'test.admin', NULL, 'Checklist for review of analytical and electronic data', '2281-13', '2281-13_fmt_2_1769670564.pdf', 'dfgdf g dfg sdfg dsf g sdfg dfg df g dfg dsf gsdf g d', 15, '::1', '2026-01-29 07:16:30'),
(10, 'test.admin', NULL, 'Main SOP Document', '1008-23', '1008-23_1769670001.pdf', 'fdgh fdg hf dgh fdgh fdg hdf gh fgdh fgd hfd gh fdgh fdg hfd gh', 16, '::1', '2026-01-29 07:16:44'),
(11, 'test.admin', NULL, 'Main SOP Document', '1008-23', '1008-23_1769670001.pdf', 'dfg dsfg dsf gd sfg dsfg dsf gdf g dfg dfg df gd fsg sdf', 15, '::1', '2026-01-29 09:23:41'),
(12, 'test.admin', NULL, 'Instrument log book', '2281-13', '2281-13_fmt_1_1769670564.pdf', 'dfg dfg dsf gsdf g dsfg df gdsf g dfg dsf g sdfg dfsg dsfg dsf g', 17, '::1', '2026-01-29 09:24:02'),
(13, 'test.user', NULL, NULL, 'F4-1008-06_1769670679.pdf', NULL, 'dfg df g df  dsfg d fg df g dfg fd g fdg fdg  df', NULL, NULL, '2026-01-29 10:46:20'),
(14, 'test.user', NULL, NULL, '2281-13_fmt_1_1769670564.pdf', NULL, 'kj nkj nij n jn jn ojk gfh fg hfg hfg h fgh fgh fg hfg', NULL, NULL, '2026-01-29 11:00:20'),
(15, 'test.user', NULL, NULL, '2266-11_1769670239.pdf', NULL, 'dxfgb dfxb dsf gdfs g dsfg dsfg dsf g dfg dfg sdf g dfsg df g', NULL, NULL, '2026-01-29 11:03:20'),
(16, 'test.user', NULL, NULL, '2281-13_fmt_1_1769670564.pdf', NULL, 'sgfdh sdfgh sdfgh sdfgh sdfgh sdfhg', NULL, NULL, '2026-01-30 04:57:57'),
(17, 'test.user', NULL, NULL, '2281-13_fmt_1_1769670564.pdf', NULL, 'rtsh  rth gfh gfdh fgh fgh', NULL, NULL, '2026-01-30 06:48:53'),
(18, 'test.user', NULL, NULL, '2266-11_1769670239.pdf', NULL, 'hj dfgj dfg hjdfg hjfdg hj fdgjh', NULL, NULL, '2026-01-30 07:02:25'),
(19, 'test.user', NULL, NULL, '2281-13_fmt_1_1769670564.pdf', NULL, 'gsfdh fgd h fdgh fgdh fgd h', NULL, NULL, '2026-01-30 07:03:00'),
(20, 'test.user', NULL, NULL, '2281-13_fmt_2_1769670564.pdf', NULL, 'gfh fdg hf gh gfh gf hgf', NULL, NULL, '2026-01-30 07:03:21'),
(21, 'test.user', NULL, NULL, '2281-13_fmt_1_1769670564.pdf', NULL, 'f gh fdgh fdg hfdg h dfgh', NULL, NULL, '2026-01-30 07:10:34');

-- --------------------------------------------------------

--
-- Table structure for table `sop_formats`
--

CREATE TABLE `sop_formats` (
  `id` int(11) NOT NULL,
  `sop_id` int(11) NOT NULL,
  `format_name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `version` varchar(10) DEFAULT '1.0',
  `status` enum('active','archived') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `sop_formats`
--

INSERT INTO `sop_formats` (`id`, `sop_id`, `format_name`, `file_name`, `version`, `status`, `created_at`) VALUES
(25, 18, 'Confidential', '001_fmt_1_1769772520.pdf', '1.0', 'archived', '2026-01-30 11:28:40'),
(26, 18, 'Confidential', '001_fmt_1769772558.pdf', '2', 'active', '2026-01-30 11:29:18'),
(27, 18, 'Confidential - 01', '001_fmt_1769772714.pdf', '1.0', 'active', '2026-01-30 11:31:54'),
(28, 18, 'Confidential - 02', '001_fmt_1769772724.pdf', '1.0', 'active', '2026-01-30 11:32:04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `emp_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('user','qa','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'user',
  `department_id` int(11) DEFAULT NULL,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `account_locked` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `user_id`, `emp_code`, `department`, `password`, `role`, `department_id`, `password_changed_at`, `last_login_at`, `failed_login_attempts`, `account_locked`, `is_active`, `created_at`, `updated_at`) VALUES
(4, 'Test Admin', 'test.admin', 'TEST001', 'IT', 'test@123', 'admin', 1, '2026-01-28 07:28:59', '2026-01-30 11:47:16', 0, 0, 1, '2026-01-27 18:27:27', '2026-01-30 11:47:16'),
(5, 'Test QA', 'test.qa', 'TEST002', 'Q.A.', 'test@123', 'qa', 2, '2026-01-28 07:28:53', '2026-01-30 11:22:15', 0, 0, 1, '2026-01-27 18:27:27', '2026-01-30 11:22:15'),
(6, 'Test User', 'test.user', 'TEST003', 'Q.C.', 'test@123', 'user', 3, '2026-01-28 07:28:45', '2026-01-30 11:45:30', 0, 0, 1, '2026-01-27 18:27:27', '2026-01-30 11:45:30'),
(8, 'Nirbhay Mali', 'Nirbhay', '32999', 'IT', 'Admin@1234', 'admin', NULL, '2026-01-28 07:27:52', NULL, 0, 0, 1, '2026-01-28 07:27:52', '2026-01-28 07:27:52'),
(9, 'Bhagyesh Patil', '@Bhagyesh', '32988', 'IT', 'test@123', 'admin', NULL, '2026-01-29 04:35:20', '2026-01-30 07:26:33', 0, 0, 1, '2026-01-29 04:35:20', '2026-01-30 07:26:33');

-- --------------------------------------------------------

--
-- Table structure for table `users_backup`
--

CREATE TABLE `users_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `emp_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users_backup`
--

INSERT INTO `users_backup` (`id`, `full_name`, `user_id`, `emp_code`, `department`, `password`) VALUES
(1, 'Bhagyesh Patil', 'hh', '85210', 'IT', 'Looks@123'),
(2, 'sachin', 'Bhagyesh122', '85236', 'hh', 'asdfg'),
(3, 'Nirbhay', 'ss', 'ss', 'ss', 'asdfgh');

-- --------------------------------------------------------

--
-- Table structure for table `user_creation`
--

CREATE TABLE `user_creation` (
  `fname` varchar(25) NOT NULL,
  `userid` varchar(6) NOT NULL,
  `empcode` int(11) NOT NULL,
  `department` varchar(10) NOT NULL,
  `password` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_creation`
--

INSERT INTO `user_creation` (`fname`, `userid`, `empcode`, `department`, `password`) VALUES
('Bhagyesh Patil', 'dfgh', 32988, 'IT', '$2y$10$dVY'),
('nirbhay', 'nirbha', 78522, 'IT', '$2y$10$v77'),
('Bhagyesh Patil', 'Bhagye', 32989, 'IT', '$2y$10$Jhh'),
('amit', 'amir21', 23588, 'IT', '$2y$10$UbB');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_trail`
--
ALTER TABLE `audit_trail`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `fileup`
--
ALTER TABLE `fileup`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sop` (`sop_number`),
  ADD KEY `idx_dept` (`department_id`);

--
-- Indexes for table `knowledge_tips`
--
ALTER TABLE `knowledge_tips`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sop_access_requests`
--
ALTER TABLE `sop_access_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_requested_at` (`requested_at`),
  ADD KEY `idx_sop_number` (`sop_number`);

--
-- Indexes for table `sop_formats`
--
ALTER TABLE `sop_formats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sop_id` (`sop_id`),
  ADD KEY `idx_sop_status` (`sop_id`,`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_trail`
--
ALTER TABLE `audit_trail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=355;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `fileup`
--
ALTER TABLE `fileup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `knowledge_tips`
--
ALTER TABLE `knowledge_tips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `sop_access_requests`
--
ALTER TABLE `sop_access_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `sop_formats`
--
ALTER TABLE `sop_formats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
