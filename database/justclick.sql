-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 02, 2025 at 05:47 AM
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
-- Database: `justclick`
--

-- --------------------------------------------------------

--
-- Table structure for table `college`
--

CREATE TABLE `college` (
  `col_id` int(10) NOT NULL,
  `col_name` varchar(999) NOT NULL,
  `col_email` varchar(128) NOT NULL,
  `col_phone` bigint(10) NOT NULL,
  `col_address` varchar(512) NOT NULL,
  `col_password` varchar(10) NOT NULL,
  `col_mode` tinyint(1) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `registered_date` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `college`
--

INSERT INTO `college` (`col_id`, `col_name`, `col_email`, `col_phone`, `col_address`, `col_password`, `col_mode`, `status`, `registered_date`) VALUES
(1, 'hncc', 'amolsolse2127@gmail.com', 37458735, 'tytty', '123', 0, 'approved', '2025-02-10'),
(3, 'dav', 'dav@gmail.com', 3132233232, 'dasfgsr', '123', 0, 'approved', '2025-02-12'),
(5, 'mim', 'amolsolse24127@gmail.com', 9834777289, 'gffgh', '@moL10', 1, 'rejected', '2025-02-20'),
(6, 'Walchand Institute Of Techonology', 'jishwar710@gmail.com', 1837438539, 'Akkalkot Road Solapur', '@moL1027', 1, 'approved', '2025-03-26');

-- --------------------------------------------------------

--
-- Table structure for table `contact_form`
--

CREATE TABLE `contact_form` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_form`
--

INSERT INTO `contact_form` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'Amol', 'amol@gmail.com', 'hii', '2025-03-28 21:53:12'),
(2, 'Amol', 'amol@gmail.com', 'hii', '2025-03-28 21:54:11'),
(3, 'Amol', 'arnav@gmaiil.com', 'hii', '2025-03-28 21:54:24'),
(4, 'Amol', 'amolsolse2127@gmail.com', 'huuu', '2025-03-28 21:59:36');

-- --------------------------------------------------------

--
-- Table structure for table `dav`
--

CREATE TABLE `dav` (
  `t_id` int(11) NOT NULL,
  `t_fname` varchar(50) NOT NULL,
  `t_lname` varchar(50) NOT NULL,
  `t_branch` varchar(32) NOT NULL,
  `t_email` varchar(255) NOT NULL,
  `t_password` varchar(255) NOT NULL,
  `t_phone` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dav`
--

INSERT INTO `dav` (`t_id`, `t_fname`, `t_lname`, `t_branch`, `t_email`, `t_password`, `t_phone`, `created_at`) VALUES
(3, 'ishwar ', 'jadhav', 'bca', 'jishwar710@gmail.com', '123', '123', '2025-03-26 05:00:18');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `t_id` int(16) NOT NULL,
  `col_name` varchar(128) NOT NULL,
  `title` varchar(250) NOT NULL,
  `branch` varchar(32) NOT NULL,
  `type` varchar(255) NOT NULL,
  `size` int(11) NOT NULL,
  `content` text NOT NULL,
  `upload_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `name`, `t_id`, `col_name`, `title`, `branch`, `type`, `size`, `content`, `upload_time`) VALUES
(5, 'WTO eco.docx', 0, '', 'Demo', '', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 11889, 'documents/WTO eco.docx', '2024-12-20 11:02:15'),
(6, 'magenta-nature-fantasy-landscape.jpg', 0, '', 'image', '', 'image/jpeg', 4219941, 'documents/magenta-nature-fantasy-landscape.jpg', '2024-12-20 11:02:15'),
(7, '360_F_680358031_rZ3bhwlPeEe081utZAkERT1Q7iUgqiml.jpg', 0, '', 'image1', '', 'image/jpeg', 39916, 'documents/360_F_680358031_rZ3bhwlPeEe081utZAkERT1Q7iUgqiml.jpg', '2024-12-20 11:02:15'),
(8, 'a3158011a18e623c4d25b68846ee9383.jpg', 0, '', 'nature', '', 'image/jpeg', 16583, 'documents/a3158011a18e623c4d25b68846ee9383.jpg', '2024-12-20 11:02:15'),
(9, '360_F_680358031_rZ3bhwlPeEe081utZAkERT1Q7iUgqiml.jpg', 0, '', 'Example', '', 'image/jpeg', 39916, 'documents/360_F_680358031_rZ3bhwlPeEe081utZAkERT1Q7iUgqiml.jpg', '2024-12-20 11:02:15'),
(10, 'ethereal-flower-minimalist-desktop-wallpaper-preview.jpg', 0, '', 'ksdgfs', '', 'image/jpeg', 48231, 'documents/ethereal-flower-minimalist-desktop-wallpaper-preview.jpg', '2024-12-20 11:02:15'),
(11, '360_F_680358031_rZ3bhwlPeEe081utZAkERT1Q7iUgqiml.jpg', 0, '', 'dtfghd', '', 'image/jpeg', 39916, 'documents/360_F_680358031_rZ3bhwlPeEe081utZAkERT1Q7iUgqiml.jpg', '2024-12-20 11:02:15'),
(12, 'Mini Project Report Submission Instructions (2).docx', 0, '', 'demo', '', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 12984, 'documents/Mini Project Report Submission Instructions (2).docx', '2024-12-20 11:02:15'),
(13, 'a3158011a18e623c4d25b68846ee9383.jpg', 0, '', 'hjbsdv', '', 'image/jpeg', 16583, 'documents/a3158011a18e623c4d25b68846ee9383.jpg', '2024-12-20 11:02:15'),
(14, 'mini project 1st page final.pdf', 0, '', 'Book 1', '', 'application/pdf', 253042, 'documents/mini project 1st page final.pdf', '2024-12-20 11:02:15'),
(15, 'magenta-nature-fantasy-landscape.jpg', 0, '', 'Book 1', '', 'image/jpeg', 4219941, 'documents/magenta-nature-fantasy-landscape.jpg', '2024-12-20 11:02:15'),
(17, 'TEH RIGHT.JPG', 0, '', 'Iimg 1', '', 'image/jpeg', 292181, 'documents/TEH RIGHT.JPG', '2024-12-20 11:02:15'),
(25, 'Resume Shriniwas Macharla.pdf', 0, 'mim', 'jss', '', 'application/pdf', 104597, 'documents/Resume Shriniwas Macharla.pdf', '2024-12-20 11:58:57'),
(26, 'ID card-21-09-2024-14_10_21.pdf', 0, 'abc', 'abc', '', 'application/pdf', 221745, 'documents/ID card-21-09-2024-14_10_21.pdf', '2024-12-23 06:03:11'),
(54, '3148.pdf', 1, 'hncc', 'php ', '', 'application/pdf', 7292105, 'documents/3148.pdf', '2025-03-12 04:15:47'),
(56, 'Amol_Solase.pdf', 1, 'hncc', 'pytho notes', 'BCA', 'application/pdf', 53723, 'documents/Amol_Solase.pdf', '2025-03-25 21:19:15'),
(57, 'Screenshot (21).png', 1, 'hncc', 'php notes', 'BCA', 'image/png', 184265, 'documents/Screenshot (21).png', '2025-03-25 21:19:22'),
(60, 'fg_project_structure_1742965404.png', 3, 'dav', 'data structures', 'BCA', 'image/png', 11393, 'documents/fg_project_structure_1742965404.png', '2025-03-26 05:03:24');

-- --------------------------------------------------------

--
-- Table structure for table `hncc`
--

CREATE TABLE `hncc` (
  `t_id` int(11) NOT NULL,
  `t_fname` varchar(50) NOT NULL,
  `t_lname` varchar(50) NOT NULL,
  `t_branch` varchar(32) NOT NULL,
  `t_email` varchar(255) NOT NULL,
  `t_password` varchar(255) NOT NULL,
  `t_phone` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hncc`
--

INSERT INTO `hncc` (`t_id`, `t_fname`, `t_lname`, `t_branch`, `t_email`, `t_password`, `t_phone`, `created_at`) VALUES
(1, 'amol ', 'solse', 'bca', 'amol@gmail.com', '123', '1', '2025-01-26 20:51:41'),
(4, 'Hemant', 'Konade', 'BCA', 'hemant@gmail.com', '213', '214', '2025-02-21 06:28:03');

-- --------------------------------------------------------

--
-- Table structure for table `homedata`
--

CREATE TABLE `homedata` (
  `id` int(11) NOT NULL,
  `username` varchar(256) NOT NULL,
  `description` varchar(999) NOT NULL,
  `links` varchar(999) NOT NULL,
  `branch` varchar(32) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `like_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `homedata`
--

INSERT INTO `homedata` (`id`, `username`, `description`, `links`, `branch`, `timestamp`, `status`, `like_count`) VALUES
(61, 'sanket', 'Java ', ' width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/A74TOX803D0?start=\" allowfullscreen', 'BCA', '2025-03-04 07:12:04', 'approved', 2),
(62, 'sanket', 'C Language', ' width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/PaPN51Mm5qQ?start=\" allowfullscreen', 'BCA', '2025-03-12 16:53:45', 'approved', 2),
(63, 'sanket', 'C++', ' width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/8jLOx1hD3_o?start=\" allowfullscreen', 'BCA', '2025-03-05 15:14:04', 'approved', 3),
(64, 'sanket', 'JavaScript', ' width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/PkZNo7MFNFg?start=\" allowfullscreen', 'BCA', '2025-02-21 16:46:01', 'approved', 3),
(65, 'sid', 'Python', ' width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/eWRfhZUzrAc?start=\" allowfullscreen', 'BCA', '2025-02-21 16:46:00', 'approved', 3),
(66, 'sid', 'Machine Learning', ' width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/i_LwzRVP7bg?start=\" allowfullscreen', 'BCA', '2025-02-21 16:45:59', 'approved', 3),
(67, 'sid', 'TensorFlow 2.0', ' width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/tPYj3fFJGjk?start=\" allowfullscreen', 'BCA', '2025-02-21 16:45:58', 'approved', 4),
(68, 'sid', 'Web Development', ' width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/kUMe1FH4CHE?start=\" allowfullscreen', 'BCA', '2025-02-21 16:45:56', 'approved', 3),
(69, 'aditya', 'PyTorch', ' width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/V_xro1bcAuA?start=\" allowfullscreen', 'BCA', '2025-02-21 16:45:51', 'approved', 4),
(70, 'ishwar', 'OOP with Python', ' width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/Ej_02ICOIgs?start=\" allowfullscreen', 'BCA', '2025-03-25 21:16:31', 'approved', 6),
(87, '1', 'l', ' width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/ImtZ5yENzgE?start=\" allowfullscreen', 'MBA', '2025-02-11 18:30:20', 'rejected', 0),
(92, 'amol', 'React', ' width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/SqcY0GlETPk?start=\" allowfullscreen', 'BBA', '2025-03-26 05:06:45', 'approved', 4),
(93, 'pritam11', 'demo videp', ' width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/SqcY0GlETPk?start=\" allowfullscreen', 'BCA', '2025-03-06 06:09:20', 'rejected', 0),
(94, 'Vaidehi', 'C++', ' width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/yGB9jhsEsr8?start=\" allowfullscreen', 'BCA', '2025-03-26 05:06:41', 'approved', 3),
(96, 'admin', 'TensorFlow', ' width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/tPYj3fFJGjk?start=\" allowfullscreen', 'BBA', '2025-03-26 06:28:32', 'approved', 0),
(97, 'amol102721', 'Docker', ' width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/zF12bwNuiw8?start=\" allowfullscreen', 'BCA', '2025-03-26 06:27:49', 'pending', 0);

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `t_id` varchar(10) NOT NULL,
  `t_name` varchar(256) NOT NULL,
  `col_name` varchar(64) NOT NULL,
  `t_email` varchar(32) NOT NULL,
  `t_branch` varchar(32) NOT NULL,
  `last_login` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`t_id`, `t_name`, `col_name`, `t_email`, `t_branch`, `last_login`, `registered_at`) VALUES
('50', 'amol solse', 'hncc', 'amol@gmail.com', 'bca', '2025-03-26 04:58:38', '2025-03-26 04:58:38'),
('4', 'Hemant Konade', 'hncc', 'hemant@gmail.com', 'BCA', '2025-02-23 17:19:55', '2025-02-23 17:19:55'),
('3', 'ishwar  jadhav', 'dav', 'jishwar710@gmail.com', 'bca', '2025-03-26 05:01:45', '2025-03-26 05:01:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstName` varchar(256) NOT NULL,
  `lastname` varchar(256) NOT NULL,
  `username` varchar(256) NOT NULL,
  `password` varchar(10) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `email` varchar(256) NOT NULL,
  `contact` bigint(20) NOT NULL,
  `DoB` date NOT NULL,
  `collegeName` varchar(300) NOT NULL,
  `branch` varchar(100) NOT NULL,
  `cur_year` varchar(64) NOT NULL,
  `timeStamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_approved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstName`, `lastname`, `username`, `password`, `gender`, `email`, `contact`, `DoB`, `collegeName`, `branch`, `cur_year`, `timeStamp`, `is_approved`) VALUES
(0, 'user1', 'us', '1', '1', 'f', 'dfamolsolse2702@gmail.com', 344, '2025-02-21', 'hncc', 'BCA', 'df', '2025-03-16 18:17:28', 1),
(1, 'John', 'Doe', 'johndoe', 'passwo', 'Male', 'john@example.com', 1234567890, '2000-01-01', 'Example College', 'CS', '3', '2025-02-01 04:30:00', 1),
(2, 'Jane', 'Smith', 'janesmith', 'passwo', 'Female', 'jane@example.com', 987654321, '2001-05-15', 'Example College', 'IT', '2', '2025-02-02 05:30:00', 1),
(52, 'server', 'admin', 'admin', 'admin1', 'm', 'admin@gmail.com', 23243443, '0000-00-00', '', '', '', '2025-02-10 08:17:21', 0),
(123, 'Amol', 'Solse', 'amol1027', '@moL1027', 'male', 'amolsolse2127@gmail.com', 9834777286, '2012-02-27', 'hncc', 'BCA', 'third-year', '2025-03-25 10:46:43', 1),
(432, 'test', 'user', '23', 'amoL10', 'male', 'amolsolse217@gmail.com', 9834777286, '2005-02-27', 'xyz', '', '', '2025-02-12 11:10:44', 0),
(545, 'pritam', 'Awatade', 'pritam', 'Pritam', 'male', 'pritamawatade.work@gmail.com', 9371364561, '2012-02-08', 'hncc', 'BCA', 'third-year', '2025-03-14 13:59:39', 1),
(1211, 'Pritam', 'Awatade', 'pritam11', 'Pritam12', 'male', '0amlsolse2127@gmail.com', 3713645866, '2005-02-02', 'hncc', 'BCA', 'third-year', '2025-03-16 18:17:42', 1),
(3010, 'Vaidehi', 'Chanmal', 'Vaidehi', 'Vaidehi123', 'female', '0amolsolse2127@gmail.com', 9891115895, '2012-02-28', 'hncc', 'BCA', 'second-year', '2025-03-16 18:17:36', 1),
(3017, 'ishwar', 'jadhav', 'ish', '123', 'male', 'amolsolse2127@gmail.com', 9834777286, '2012-02-28', 'dav', 'BCA', 'first-year', '2025-03-29 06:28:45', 1),
(3099, 'ISHWAR', 'JADHAV', 'ish2', 'Pass@123', 'male', 'jishwar710@gmail.com', 233232343, '2012-03-28', 'hncc', 'BCA', 'third-year', '2025-03-29 07:06:08', 1),
(3148, 'amol', 'solase', 'amol102721', '@moL102721', 'male', 'anilsakhare4@gmail.com', 1234567890, '2012-03-15', 'hncc', 'BCA', 'third-year', '2025-03-26 06:20:52', 1),
(3536, 'amol', 'solse', 'amol', '111111', 'male', 'amol@gmail.com', 543653654654, '2024-12-05', 'hncc', 'BCA', 'third-year', '2025-03-26 04:33:32', 1);

-- --------------------------------------------------------

--
-- Table structure for table `video_likes`
--

CREATE TABLE `video_likes` (
  `like_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `video_likes`
--

INSERT INTO `video_likes` (`like_id`, `user_id`, `video_id`, `created_at`) VALUES
(5, 3536, 66, '2025-02-08 21:36:25'),
(6, 3536, 64, '2025-02-08 21:36:28'),
(7, 3536, 70, '2025-02-08 21:38:00'),
(9, 3536, 69, '2025-02-08 21:45:41'),
(10, 3536, 67, '2025-02-08 21:45:45'),
(30, 545, 69, '2025-02-10 08:12:58'),
(31, 545, 70, '2025-02-10 08:13:00'),
(36, 3536, 63, '2025-02-11 17:37:06'),
(37, 3536, 65, '2025-02-11 17:37:08'),
(38, 3536, 68, '2025-02-11 17:37:10'),
(42, 432, 67, '2025-02-12 11:31:08'),
(43, 545, 92, '2025-02-20 17:50:49'),
(44, 0, 69, '2025-02-21 16:45:51'),
(45, 0, 70, '2025-02-21 16:45:53'),
(46, 0, 92, '2025-02-21 16:45:55'),
(47, 0, 68, '2025-02-21 16:45:56'),
(48, 0, 67, '2025-02-21 16:45:58'),
(49, 0, 66, '2025-02-21 16:45:59'),
(50, 0, 65, '2025-02-21 16:46:00'),
(51, 0, 64, '2025-02-21 16:46:01'),
(54, 1211, 70, '2025-03-03 06:33:16'),
(55, 0, 61, '2025-03-04 07:12:04'),
(58, 0, 63, '2025-03-05 15:14:04'),
(59, 0, 94, '2025-03-11 05:53:08'),
(60, 0, 62, '2025-03-12 16:53:45'),
(61, 123, 94, '2025-03-25 21:15:55'),
(62, 123, 70, '2025-03-25 21:16:31'),
(63, 123, 92, '2025-03-25 21:16:33'),
(64, 3536, 94, '2025-03-26 05:06:41'),
(65, 3536, 92, '2025-03-26 05:06:45');

-- --------------------------------------------------------

--
-- Table structure for table `walchand institute of techonology`
--

CREATE TABLE `walchand institute of techonology` (
  `t_id` int(11) NOT NULL,
  `t_fname` varchar(50) NOT NULL,
  `t_lname` varchar(50) NOT NULL,
  `t_branch` varchar(32) NOT NULL,
  `t_email` varchar(255) NOT NULL,
  `t_password` varchar(255) NOT NULL,
  `t_phone` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `college`
--
ALTER TABLE `college`
  ADD PRIMARY KEY (`col_id`),
  ADD UNIQUE KEY `col_email` (`col_email`),
  ADD UNIQUE KEY `col_name` (`col_name`) USING HASH;

--
-- Indexes for table `contact_form`
--
ALTER TABLE `contact_form`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dav`
--
ALTER TABLE `dav`
  ADD PRIMARY KEY (`t_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hncc`
--
ALTER TABLE `hncc`
  ADD PRIMARY KEY (`t_id`);

--
-- Indexes for table `homedata`
--
ALTER TABLE `homedata`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_homedata_likes` (`like_count`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`t_email`),
  ADD UNIQUE KEY `t_id` (`t_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `video_likes`
--
ALTER TABLE `video_likes`
  ADD PRIMARY KEY (`like_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_video_likes` (`video_id`);

--
-- Indexes for table `walchand institute of techonology`
--
ALTER TABLE `walchand institute of techonology`
  ADD PRIMARY KEY (`t_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `college`
--
ALTER TABLE `college`
  MODIFY `col_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `contact_form`
--
ALTER TABLE `contact_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `dav`
--
ALTER TABLE `dav`
  MODIFY `t_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `hncc`
--
ALTER TABLE `hncc`
  MODIFY `t_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `homedata`
--
ALTER TABLE `homedata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `video_likes`
--
ALTER TABLE `video_likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `walchand institute of techonology`
--
ALTER TABLE `walchand institute of techonology`
  MODIFY `t_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `video_likes`
--
ALTER TABLE `video_likes`
  ADD CONSTRAINT `video_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `video_likes_ibfk_2` FOREIGN KEY (`video_id`) REFERENCES `homedata` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
