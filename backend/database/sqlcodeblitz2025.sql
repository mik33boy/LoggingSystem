-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2025 at 06:08 PM
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
-- Database: `sqlcodeblitz2025`
--

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `direction` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `sender` varchar(255) DEFAULT NULL,
  `log_by` varchar(255) DEFAULT NULL,
  `confidentiality_level` varchar(50) DEFAULT 'public',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `client_name` varchar(255) NOT NULL,
  `attachment` varchar(255) NOT NULL,
  `confidential_key` int(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `direction`, `type`, `subject`, `content`, `sender`, `log_by`, `confidentiality_level`, `created_at`, `client_name`, `attachment`, `confidential_key`) VALUES
(3, 3, 'Incoming', 'Email', 'UI Error', 'Meta AI is experiencing a problem with its user interface, leading to a less effective or disrupted user experience. The issue may involve layout glitches, navigation problems, or other visual or functional inconsistencies that hinder user interaction with the platform, requiring prompt resolution for optimal usability and performance.\n', 'meta@facebook.com', 'Michael Dayandante', 'public', '2025-05-27 13:38:42', 'Meta Company', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `logs_replies`
--

CREATE TABLE `logs_replies` (
  `logs_rep` int(255) NOT NULL,
  `log_subject` varchar(255) NOT NULL,
  `logs_description` varchar(255) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `logged_by` varchar(255) NOT NULL,
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `user_id` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs_replies`
--

INSERT INTO `logs_replies` (`logs_rep`, `log_subject`, `logs_description`, `client_name`, `logged_by`, `created_at`, `user_id`) VALUES
(1, 'UI Error', 'UI not loading properly in Meta AI dashboard', 'Meta Company', 'John Doe', '2025-05-27 16:06:40.419393', 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `token`, `created_at`, `firstname`, `lastname`, `email`, `role`) VALUES
(3, 'mike2330', '$2y$10$rhHM165ig8/mHRLBEAscJOH/GlZEhTiyQdtgazSLty.UQ2M7JIT6e', 'e6f896bae2bf0fac0e1d2a65e194cd96c093784597732c7e5122dd6b2d36393d', '2025-05-26 14:09:36', 'Michael', 'Dayandante', 'mike@gmail.com', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `logs_replies`
--
ALTER TABLE `logs_replies`
  ADD PRIMARY KEY (`logs_rep`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username`