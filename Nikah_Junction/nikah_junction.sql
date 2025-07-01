-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 06, 2025 at 04:15 AM
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
-- Database: `nikah_junction`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact_us`
--

CREATE TABLE `contact_us` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `date_submitted` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_us`
--

INSERT INTO `contact_us` (`id`, `email`, `subject`, `message`, `date_submitted`) VALUES
(6, 'ali@gamil.com', 'hi', 'hello how are you ', '2025-05-01 13:24:13'),
(13, 'khushal@gamil.com', 'hi', 'jnhbnhbgbgbgbgbgbbbg', '2025-05-01 13:37:52'),
(14, 'khushaldas404@gmail.com', 'helo i am ali ', 'hello', '2025-05-01 14:01:37'),
(15, 'ali@gamil.com', 'helo i am ali ', 'hello', '2025-05-01 14:08:16'),
(16, 'hh@gmail.com', 'leave', 'Good Bye', '2025-05-01 15:00:55'),
(17, 'hh@gmail.com', 'leave', 'Good Bye', '2025-05-01 15:38:54');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `conversation_id` int(11) NOT NULL,
  `last_message_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`conversation_id`, `last_message_id`, `created_at`, `updated_at`) VALUES
(1, NULL, '2025-04-20 03:30:00', '2025-04-25 09:22:00'),
(2, NULL, '2025-04-21 05:15:00', '2025-04-26 04:45:00'),
(3, NULL, '2025-04-22 07:00:00', '2025-04-26 11:10:00'),
(4, NULL, '2025-04-22 08:45:00', '2025-04-27 06:30:00'),
(5, NULL, '2025-04-23 04:20:00', '2025-04-27 13:05:00'),
(6, NULL, '2025-04-23 09:10:00', '2025-04-28 02:15:00'),
(7, NULL, '2025-04-24 06:30:00', '2025-04-28 03:30:00'),
(8, NULL, '2025-04-24 11:45:00', '2025-04-28 05:20:00'),
(9, NULL, '2025-04-25 05:00:00', '2025-04-28 07:40:00'),
(10, NULL, '2025-04-25 10:30:00', '2025-04-28 08:15:00'),
(11, 5, '2025-05-01 03:53:07', '2025-05-01 05:33:52'),
(12, 13, '2025-05-01 13:34:51', '2025-05-01 13:40:03');

-- --------------------------------------------------------

--
-- Table structure for table `conversation_members`
--

CREATE TABLE `conversation_members` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversation_members`
--

INSERT INTO `conversation_members` (`id`, `conversation_id`, `user_id`, `created_at`) VALUES
(1, 11, 13, '2025-05-01 03:53:07'),
(2, 11, 8, '2025-05-01 03:53:07'),
(3, 12, 15, '2025-05-01 13:34:51'),
(4, 12, 13, '2025-05-01 13:34:51');

-- --------------------------------------------------------

--
-- Table structure for table `deleted_users_log`
--

CREATE TABLE `deleted_users_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `deletion_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deleted_users_log`
--

INSERT INTO `deleted_users_log` (`id`, `user_id`, `deletion_date`) VALUES
(1, 12, '2025-04-28 13:13:53');

-- --------------------------------------------------------

--
-- Table structure for table `friend_requests`
--

CREATE TABLE `friend_requests` (
  `request_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `friend_requests`
--

INSERT INTO `friend_requests` (`request_id`, `sender_id`, `receiver_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 13, 5, 'rejected', '2025-04-29 14:59:11', '2025-04-30 22:56:42'),
(2, 13, 14, 'rejected', '2025-04-30 08:45:06', '2025-04-30 22:49:22'),
(3, 13, 11, '', '2025-04-30 09:06:08', '2025-05-01 10:42:50'),
(4, 13, 8, 'accepted', '2025-04-30 09:19:55', '2025-04-30 12:30:12'),
(5, 13, 1, '', '2025-04-30 21:59:50', '2025-04-30 21:59:50'),
(6, 15, 13, 'accepted', '2025-05-01 08:47:03', '2025-05-01 11:08:18'),
(7, 15, 14, 'accepted', '2025-05-01 06:07:48', '2025-05-01 11:08:23'),
(8, 18, 15, 'pending', '2025-05-01 11:46:01', '2025-05-01 11:46:01'),
(9, 28, 10, '', '2025-05-01 13:40:33', '2025-05-01 13:40:33'),
(10, 28, 13, 'rejected', '2025-05-01 13:40:39', '2025-05-01 13:40:39');

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`email`, `password`, `status`) VALUES
('ahmed.ali01100001@gmail.com', '$2y$10$j6up8owWgZGGIasXexKDT.3BrLhJq75xihAGHNq/gfQVybwf/eXba', 0),
('khushaldas404@gmail.com', '$2y$10$j4YncDdxvCJe/vlqQuw9buK9/pTX8eWfEipsOP68Adz5zXyGnkKP6', 0);

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `match_id` int(11) NOT NULL,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `matched_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `match_status` enum('active','inactive','married') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `matching_parameters`
--

CREATE TABLE `matching_parameters` (
  `parameter_id` int(11) NOT NULL,
  `parameter_name` varchar(50) NOT NULL,
  `parameter_value` float NOT NULL,
  `parameter_weight` float NOT NULL,
  `parameter_category` varchar(30) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `matching_parameters`
--

INSERT INTO `matching_parameters` (`parameter_id`, `parameter_name`, `parameter_value`, `parameter_weight`, `parameter_category`, `created_at`, `updated_at`) VALUES
(1, 'religion_match', 1, 10, 'religious', '2025-04-29 03:25:51', '2025-04-29 03:25:51'),
(2, 'caste_match', 1, 7, 'religious', '2025-04-29 03:25:51', '2025-04-29 03:25:51'),
(3, 'mother_tongue_match', 1, 5, 'cultural', '2025-04-29 03:25:51', '2025-04-29 03:25:51'),
(4, 'age_difference', 1, 6, 'demographics', '2025-04-29 03:25:51', '2025-04-29 03:25:51'),
(5, 'height_compatibility', 1, 4, 'demographics', '2025-04-29 03:25:51', '2025-04-29 03:25:51'),
(6, 'location_match', 1, 6, 'demographics', '2025-04-29 03:25:51', '2025-04-29 03:25:51'),
(7, 'diet_match', 1, 4, 'lifestyle', '2025-04-29 03:25:51', '2025-04-29 03:25:51'),
(8, 'drinking_match', 1, 5, 'lifestyle', '2025-04-29 03:25:51', '2025-04-29 03:25:51'),
(9, 'smoking_match', 1, 5, 'lifestyle', '2025-04-29 03:25:51', '2025-04-29 03:25:51'),
(10, 'education_level_match', 1, 5, 'education', '2025-04-29 03:25:51', '2025-04-29 03:25:51'),
(11, 'occupation_category_match', 1, 4, 'career', '2025-04-29 03:25:51', '2025-04-29 03:25:51'),
(12, 'family_background_match', 1, 3, 'family', '2025-04-29 03:25:51', '2025-04-29 03:25:51');

-- --------------------------------------------------------

--
-- Table structure for table `match_requests`
--

CREATE TABLE `match_requests` (
  `request_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `match_requests`
--

INSERT INTO `match_requests` (`request_id`, `sender_id`, `receiver_id`, `status`, `created_at`, `updated_at`) VALUES
(2, 18, 15, 'pending', '2025-05-01 11:45:48', '2025-05-01 11:45:48'),
(4, 15, 5, 'pending', '2025-05-01 11:53:03', '2025-05-01 11:53:03'),
(5, 15, 6, 'pending', '2025-05-01 13:37:58', '2025-05-01 13:37:58'),
(6, 13, 15, 'pending', '2025-05-01 13:41:05', '2025-05-01 13:41:05');

-- --------------------------------------------------------

--
-- Table structure for table `match_scores`
--

CREATE TABLE `match_scores` (
  `score_id` int(11) NOT NULL,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `match_score` float NOT NULL,
  `compatibility_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`compatibility_details`)),
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `conversation_id`, `sender_id`, `recipient_id`, `message_text`, `is_read`, `created_at`) VALUES
(1, 11, 13, 8, 'hi', 0, '2025-05-01 03:53:58'),
(2, 11, 13, 8, 'how are you', 0, '2025-05-01 04:01:05'),
(3, 11, 13, 8, 'ahmed', 0, '2025-05-01 04:15:47'),
(4, 11, 13, 8, 'hi', 0, '2025-05-01 05:16:02'),
(5, 11, 13, 8, 'hello', 0, '2025-05-01 05:33:52'),
(11, 12, 15, 13, 'hi', 1, '2025-05-01 13:35:08'),
(12, 12, 13, 15, 'hello', 0, '2025-05-01 13:39:51'),
(13, 12, 13, 15, 'keesy ho', 0, '2025-05-01 13:40:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `email`, `phone`, `password_hash`, `created_at`, `last_login`, `is_active`, `status`) VALUES
(1, 'ali', 'Das', 'ali@gamil.com', '+92-340-1241204', '$2y$10$0bzfG7kV.ybzfrm5rRW1j.x0EDJVNvysqwqgBXNjX7RkA56fw5VNK', '2025-04-27 14:11:24', NULL, 1, 1),
(3, 'ali', 'Das', 'ali@sdf.com', '+92-340-1241204', '$2y$10$3shidGmLp2ZWUJJ/C75zceDE8OMA4dpxJqyS6l9U1c.hegTW6q8aC', '2025-04-27 14:12:37', NULL, 1, 1),
(5, 'KHUSHAL', 'KHUSHAL', 'asdali@gamil.com', '+92-340-1241204', '$2y$10$QDUZyKkC3X87fR6J/YxIg.xDDAWIc0osE9/G8DQXQG7OvVSQa0ETe', '2025-04-28 00:21:09', NULL, 1, 1),
(6, 'hello', 'hi', 'hello@gmail.com', '+92-340-4986167', '$2y$10$lAeqJFTsnnUDmAsnauWyfOjBgM5YYWFR6nlrJ9Knt2/vJutRhpkAq', '2025-04-28 00:58:04', NULL, 1, 1),
(7, 'nikah_junction', 'hi', 'jikah@gmail.com', '+92-324-0385935', '$2y$10$CxWLxtXJqBocLk5aVnQPkuG3m0PfDXbJUDZcANVnwEP0sa9IUjC.e', '2025-04-28 01:41:11', NULL, 1, 1),
(8, 'nikah_junction', 'hi', 'jikah2@gmail.com', '+92-324-0385935', '$2y$10$Csk7Btgv4SwfCxEu.paYAeT.c4bgS3ITBk0QAlE03n6s/Bf944hg.', '2025-04-28 01:49:04', NULL, 1, 1),
(10, 'nikah_junction', 'hi', 'jika1h2@gmail.com', '+92-656-7465456', '$2y$10$LSD2tWh00ODhPR23RMKWoem0kVL.2xDD/Ap.SZjUxS9mJhD00LCoq', '2025-04-28 02:07:16', NULL, 1, 1),
(11, 'haersafasdf', 'sdafujhds', 'asdfn@gmail.com', '+92-392-7483274', '$2y$10$aHwDxXNJLLZOEOCXvacOOOgrZ83qyXb7OCg63W6oiMVj7lBP5LAhm', '2025-04-28 02:23:17', NULL, 1, 1),
(13, 'Ahmed ', 'Ali', 'ahmed.ali01100001@gmail.com', '+92-336-9111552', '$2y$10$VQmQ7wQaCE6BFA7IrlY6vec1ZikCrwOsqbBJ5WN9VNLKTiCGuw07a', '2025-04-28 13:19:06', NULL, 0, 1),
(14, 'roshan', 'jagdesh', 'roshankumar@gmail.com', '+92-341-3467134', '$2y$10$zl9ZZQUpB3uWFqKpMph81.FaSj2tBinxiYVTQzb9Jw.7o22chaqma', '2025-04-29 12:58:48', NULL, 1, 1),
(15, 'jalal  ', 'noor', 'jalal.noor@gmail.com', '+92-315-6638665', '$2y$10$SgH9LSx3gUb.ywO1.bSb1usZF/NDUODqc6ulVJ9k2lwvpJpN3x8sG', '2025-05-01 08:40:36', NULL, 1, 1),
(17, 'Ahmed', 'Ali', 'a.ali01100001@gmail.com', '+92-336-9111552', '$2y$10$6uR4X9DWpdmqmEeAFreba.XV.NVXuvDkh82y8uJqLOPOg8qrnI066', '2025-05-01 11:39:58', NULL, 1, 1),
(18, 'Ahmed', 'Ali', 'a1.ali01100001@gmail.com', '+92-336-9111552', '$2y$10$9NWopKdeh6Y/7kln/FhPh.rjDwYrqoWwBqcIIy/z5/V5eAFJeVLD.', '2025-05-01 11:41:20', NULL, 1, 1),
(28, 'khushal', 'das', 'khushaldas404@gmail.com', '+92-340-3986167', '$2y$10$BSSIX728.6xvzAwAyhkgMedXJXtGPHVEJnY66D5SpzKyJ/DI12BZm', '2025-05-01 16:35:55', NULL, 1, 1),
(29, 'abb', 'bccc', '22p9318@pwr.nu.edu.pk', '+92-123-484994', '$2y$10$Y1LnyGSOMGqkP4jahM3MROm/w7m.5vdY.yWPfVt55FASej5n5G/P2', '2025-05-02 04:23:09', NULL, 1, 0),
(32, 'aaa', 'bbb', 'jotix30649@idoidraw.com', '+92-336-9111553', '$2y$10$zxn1v4nhLHlRMZhr9jj6SusDWH8nD6dJ7YURJip4EZ48I6KZYT3zS', '2025-05-02 04:39:57', NULL, 1, 0),
(33, 'Khushal', 'Das', 'aw3yt1mz54@vafyxh.com', '+92-100-1111010', '$2y$10$/i9ljpvm6.oqxaC3pfqEvuxlSuD43Hk0/a1fnhULa/DzCgW7VsLim', '2025-05-02 05:13:31', NULL, 1, 0),
(34, 'Khushal', 'Das', 'ml9gr5sfks@ibolinva.com', '+92-100-1111010', '$2y$10$Fj3ZVF7nZkwOw5CDGBimV.w.RL0WLyxjb5GhDqth2Vxl3aGQ8tSdu', '2025-05-02 05:16:52', NULL, 1, 0),
(35, 'Khushal', 'Das', '3d8n4kjkyp@wyoxafp.com', '+92-321-9752213', '$2y$10$45WMetEqvi4kf..90ibtE.OQinrGxdrvYCkF4bhiV4dtI.dHSipCu', '2025-05-02 05:37:00', NULL, 1, 1),
(36, 'abc', 'xyz', 'tes0dgau1j@zudpck.com', '+92-336-9111552', '$2y$10$zNkru0Cn4I5uaeOnLg/imepB0rZ9dsZJL75KyjaDJvkR5Ytfmsm6W', '2025-05-02 06:32:54', NULL, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_log`
--

CREATE TABLE `user_activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_activity_log`
--

INSERT INTO `user_activity_log` (`id`, `user_id`, `action`, `timestamp`) VALUES
(1, 13, 'account_deactivated', '2025-04-29 11:53:41'),
(2, 13, 'account_deactivated', '2025-04-30 11:14:50'),
(3, 15, 'cancelled_match_request', '2025-05-01 12:27:38'),
(4, 15, 'sent_match_request', '2025-05-01 13:37:58'),
(5, 13, 'sent_match_request', '2025-05-01 13:41:05');

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) DEFAULT 0,
  `sms_notifications` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `dob` date NOT NULL,
  `gender` varchar(10) NOT NULL,
  `religion` varchar(50) NOT NULL,
  `caste` varchar(50) NOT NULL,
  `mother_tongue` varchar(50) NOT NULL,
  `height` varchar(10) NOT NULL,
  `weight` int(11) NOT NULL,
  `country` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `education` varchar(50) NOT NULL,
  `employment` varchar(50) NOT NULL,
  `occupation` varchar(100) NOT NULL,
  `marital_status` varchar(20) NOT NULL,
  `diet` varchar(20) NOT NULL,
  `drinking` varchar(20) NOT NULL,
  `smoking` varchar(20) NOT NULL,
  `father_occupation` varchar(100) DEFAULT NULL,
  `mother_occupation` varchar(100) DEFAULT NULL,
  `siblings` int(11) DEFAULT NULL,
  `min_age_pref` int(11) NOT NULL,
  `max_age_pref` int(11) NOT NULL,
  `min_height_pref` varchar(10) NOT NULL,
  `max_height_pref` varchar(10) NOT NULL,
  `additional_preferences` text DEFAULT NULL,
  `divorce_years` int(11) DEFAULT NULL,
  `widowed_years` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `visibility` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `profile_picture`, `dob`, `gender`, `religion`, `caste`, `mother_tongue`, `height`, `weight`, `country`, `address`, `education`, `employment`, `occupation`, `marital_status`, `diet`, `drinking`, `smoking`, `father_occupation`, `mother_occupation`, `siblings`, `min_age_pref`, `max_age_pref`, `min_height_pref`, `max_height_pref`, `additional_preferences`, `divorce_years`, `widowed_years`, `created_at`, `visibility`) VALUES
(1, '', '2009-06-16', 'Male', 'Islam', 'sajnfljan', 'asjfk', '4\'9', 120, 'PAKISTAN', 'dasjnfjlsna Road, Lahore', 'Matriculation', 'Employed', 'N/A', 'Never Married', 'Vegetarian', 'No', 'No', 'asjnflkjas', 'askjfkjsa', 1, 22, 30, '4\'10', '5\'1', 'Interests: Cooking, Travel, Reading\r\n\r\n', NULL, NULL, '2025-04-28 02:04:45', NULL),
(2, '', '2003-02-12', 'Male', 'Christianity', 'sadasf', 'asjfk', '4\'11', 100, 'USA', 'dasjnfjlsna Road, Lahore', 'Intermediate', 'Unemployed', 'N/A', 'Never Married', 'Non-Vegetarian', 'No', 'Occasionally', 'asjnflkjas', 'askjfkjsa', 1, 22, 25, '4\'10', '5\'3', 'Interests: Cooking, Travel, Reading\r\n\r\n', NULL, NULL, '2025-04-28 02:08:37', NULL),
(3, '', '2003-02-12', 'Male', 'Christianity', 'sadasf', 'asjfk', '4\'11', 100, 'USA', 'dasjnfjlsna Road, Lahore', 'Intermediate', 'Unemployed', 'N/A', 'Never Married', 'Non-Vegetarian', 'No', 'Occasionally', 'asjnflkjas', 'askjfkjsa', 1, 22, 25, '4\'10', '5\'3', 'Interests: Cooking, Travel, Reading\r\n\r\n', NULL, NULL, '2025-04-28 02:09:30', NULL),
(4, '', '2001-01-02', 'Male', 'Islam', 'sajnfljan', 'asjfk', '5\'5', 111, 'PAKISTAN', 'dasjnfjlsna Road, Lahore', 'Bachelors', 'Employed', 'N/A', 'Never Married', 'Vegetarian', 'No', 'No', 'asjnflkjas', 'askjfkjsa', 1, 25, 35, '4\'7', '5\'4', 'Interests: Cooking, Travel, Reading\r\nwtiawesjfojjdshngvjon\r\n', NULL, NULL, '2025-04-28 02:30:18', NULL),
(5, '', '2001-01-02', 'Male', 'Islam', 'sajnfljan', 'asjfk', '5\'5', 111, 'PAKISTAN', 'dasjnfjlsna Road, Lahore', 'Bachelors', 'Employed', 'N/A', 'Never Married', 'Vegetarian', 'No', 'No', 'asjnflkjas', 'askjfkjsa', 1, 25, 35, '4\'7', '5\'4', 'Interests: Cooking, Travel, Reading\r\nwtiawesjfojjdshngvjon\r\n', NULL, NULL, '2025-04-28 02:32:30', NULL),
(6, '', '2001-01-02', 'Male', 'Islam', 'sajnfljan', 'asjfk', '5\'5', 111, 'PAKISTAN', 'dasjnfjlsna Road, Lahore', 'Bachelors', 'Employed', 'N/A', 'Never Married', 'Vegetarian', 'No', 'No', 'asjnflkjas', 'askjfkjsa', 1, 25, 35, '4\'7', '5\'4', 'Interests: Cooking, Travel, Reading\r\nwtiawesjfojjdshngvjon\r\n', NULL, NULL, '2025-04-28 02:32:49', NULL),
(7, '', '2001-01-01', 'Male', 'Islam', 'sadasf', 'dsffd', '5\'0', 122, 'PAKISTAN', 'dasjnfjlsna Road, Lahore', 'Matriculation', 'Unemployed', '', 'Never Married', 'Non-Vegetarian', 'No', 'No', 'asjnflkjas', 'askjfkjsa', 1, 25, 35, '5\'1', '5\'7', 'Interests: Cooking, Travel, Reading\r\n\r\n', NULL, NULL, '2025-04-28 03:06:07', NULL),
(13, '', '2003-01-14', 'Male', 'Islam', 'Awan', 'Urdu', '5\'1', 70, 'PAKISTAN', 'G/9-2', 'Bachelors', 'Unemployed', '', 'Never Married', 'Non-Vegetarian', 'No', 'No', 'NIL', 'NIL', 0, 18, 21, '4\'9', '5\'3', '', NULL, NULL, '2025-04-28 13:25:39', NULL),
(14, '', '2006-02-24', 'Male', 'Hinduism', 'bheel', 'dhatki', '5\'6', 49, 'PAKISTAN', 'near SF college mithi ', 'Bachelors', 'Unemployed', '', 'Never Married', 'Non-Vegetarian', 'No', 'No', 'private', 'NIL', 4, 25, 35, '5\'5', '5\'7', '', NULL, NULL, '2025-04-29 13:06:03', NULL),
(15, '', '2006-04-05', 'Male', 'Islam', 'awan', 'pushto', '4\'7', 67, 'PAKISTAN', 'awiass hostel', 'Bachelors', 'Unemployed', '', 'Never Married', 'Non-Vegetarian', 'Occasionally', 'Occasionally', 'NIL', 'NIL', 5, 27, 35, '4\'7', '5\'1', 'reading ', NULL, NULL, '2025-05-01 08:43:35', NULL),
(16, '', '2000-01-02', 'Male', 'Islam', 'awan', 'pushto', '5\'5', 67, 'INDIA', 'Islamabad', 'Intermediate', 'Employed', 'N/A', 'Never Married', 'Eggetarian', 'Yes', 'Yes', 'NIL', 'NIL', 1, 25, 35, '4\'10', '5\'7', 'xyz', NULL, NULL, '2025-05-01 11:45:12', NULL),
(17, '', '2001-01-02', 'Male', 'Hinduism', 'bheel', 'Urdu', '5\'2', 65, 'PAKISTAN', 'MIthi', 'Bachelors', 'Employed', 'Teacher', 'Never Married', 'Vegetarian', 'No', 'No', 'Nill', 'nill', 2, 25, 35, '4\'8', '5\'4', 'Cooking\r\n Travel\r\n Reading\r\n Sports\r\n Music\r\n Art\r\n Movies\r\n Photography\r\n Nature\r\n Technology', NULL, NULL, '2025-05-01 16:39:29', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_us`
--
ALTER TABLE `contact_us`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`conversation_id`),
  ADD KEY `fk_conversations_last_message` (`last_message_id`);

--
-- Indexes for table `conversation_members`
--
ALTER TABLE `conversation_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_conversation_user` (`conversation_id`,`user_id`),
  ADD KEY `fk_conversation_members_user` (`user_id`);

--
-- Indexes for table `deleted_users_log`
--
ALTER TABLE `deleted_users_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD UNIQUE KEY `unique_request` (`sender_id`,`receiver_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`match_id`),
  ADD UNIQUE KEY `unique_match_pair` (`user1_id`,`user2_id`),
  ADD KEY `user2_id` (`user2_id`);

--
-- Indexes for table `matching_parameters`
--
ALTER TABLE `matching_parameters`
  ADD PRIMARY KEY (`parameter_id`);

--
-- Indexes for table `match_requests`
--
ALTER TABLE `match_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD UNIQUE KEY `unique_request` (`sender_id`,`receiver_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `match_scores`
--
ALTER TABLE `match_scores`
  ADD PRIMARY KEY (`score_id`),
  ADD UNIQUE KEY `unique_match` (`user1_id`,`user2_id`),
  ADD KEY `user2_id` (`user2_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `fk_messages_conversation` (`conversation_id`),
  ADD KEY `fk_messages_sender` (`sender_id`),
  ADD KEY `fk_messages_recipient` (`recipient_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact_us`
--
ALTER TABLE `contact_us`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `conversation_members`
--
ALTER TABLE `conversation_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `deleted_users_log`
--
ALTER TABLE `deleted_users_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `friend_requests`
--
ALTER TABLE `friend_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `match_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `matching_parameters`
--
ALTER TABLE `matching_parameters`
  MODIFY `parameter_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `match_requests`
--
ALTER TABLE `match_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `match_scores`
--
ALTER TABLE `match_scores`
  MODIFY `score_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `fk_conversations_last_message` FOREIGN KEY (`last_message_id`) REFERENCES `messages` (`message_id`) ON DELETE SET NULL;

--
-- Constraints for table `conversation_members`
--
ALTER TABLE `conversation_members`
  ADD CONSTRAINT `fk_conversation_members_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`conversation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conversation_members_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `friend_requests`
--
ALTER TABLE `friend_requests`
  ADD CONSTRAINT `friend_requests_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `friend_requests_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `match_requests`
--
ALTER TABLE `match_requests`
  ADD CONSTRAINT `match_requests_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `match_requests_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `match_scores`
--
ALTER TABLE `match_scores`
  ADD CONSTRAINT `match_scores_ibfk_1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `match_scores_ibfk_2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`conversation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_messages_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
