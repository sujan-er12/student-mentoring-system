-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 27, 2025 at 05:42 PM
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
-- Database: `student_mentoring_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `mentor_id` int(11) NOT NULL,
  `feedback_text` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `student_id`, `mentor_id`, `feedback_text`, `submitted_at`) VALUES
(1, 1, 1, 'I am finding the current coursework challenging.', '2025-01-16 17:19:10'),
(9, 4, 3, 'knz', '2025-02-27 15:40:51'),
(10, 4, 3, 'jbz', '2025-02-27 15:44:55'),
(11, 4, 3, 'jbz', '2025-02-27 15:46:55');

-- --------------------------------------------------------

--
-- Table structure for table `meetings`
--

CREATE TABLE `meetings` (
  `meeting_id` int(11) NOT NULL,
  `mentor_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `meeting_date` datetime NOT NULL,
  `meeting_purpose` text DEFAULT NULL,
  `notification_status` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meetings`
--

INSERT INTO `meetings` (`meeting_id`, `mentor_id`, `student_id`, `meeting_date`, `meeting_purpose`, `notification_status`) VALUES
(2, 1, 1, '2025-02-27 18:48:00', 'knbsi', 0),
(3, 2, 3, '2025-02-27 22:35:00', 'knsix', 0),
(4, 3, 4, '2025-02-28 02:41:00', 'fee', 0);

-- --------------------------------------------------------

--
-- Table structure for table `mentors`
--

CREATE TABLE `mentors` (
  `mentor_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `specialization` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mentors`
--

INSERT INTO `mentors` (`mentor_id`, `user_id`, `name`, `department`, `contact_number`, `email`, `specialization`) VALUES
(1, 1, 'Dr. John Doe', 'Computer Science', '1234567890', 'mentor1@example.com', 'AI & Machine Learning'),
(2, 11, 'sagar', NULL, NULL, 'a@mail.co', NULL),
(3, 16, 'sudeep', NULL, NULL, 'sudeep@gmail.com', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `notification_type` enum('meeting','feedback','general') DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `message`, `notification_type`, `sent_at`) VALUES
(1, 2, 'You have a scheduled meeting on 2025-01-20.', 'meeting', '2025-01-16 17:19:10'),
(2, 2, 'Your mentor has scheduled a meeting on 27 Feb 2025, 06:48 PM. Purpose: knbsi', 'meeting', '2025-02-27 13:18:48'),
(3, 1, 'jkbsx', 'general', '2025-02-27 13:18:58'),
(4, 1, 'jsbdvx', 'general', '2025-02-27 13:34:14'),
(5, 13, 'Your mentor has scheduled a meeting on 27 Feb 2025, 08:35 PM. Purpose: knsix', 'meeting', '2025-02-27 15:05:34'),
(6, 11, 'sx ', 'general', '2025-02-27 15:09:29'),
(7, 17, 'Your mentor has scheduled a meeting on 28 Feb 2025, 02:41 AM. Purpose: fee', 'meeting', '2025-02-27 15:11:24'),
(8, 16, 'tommorrow holiday', 'general', '2025-02-27 15:11:34');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `mentor_id` int(11) DEFAULT NULL,
  `academic_performance` text DEFAULT NULL,
  `last_submitted` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `name`, `date_of_birth`, `gender`, `course`, `contact_number`, `mentor_id`, `academic_performance`, `last_submitted`) VALUES
(1, 2, 'Jane Smith', '2002-05-12', 'female', 'MCA', '9876543210', 1, 'Good progress in academics.', '2025-02-27 13:58:23'),
(3, 13, 'Anoop', NULL, NULL, 'BCA', NULL, 2, NULL, NULL),
(4, 17, 'Prajwal', NULL, NULL, 'MCA', NULL, 3, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mentor','student','admin') NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `email`, `created_at`) VALUES
(1, 'mentor1', 'mentor1pass', 'mentor', 'mentor1@example.com', '2025-01-16 17:19:10'),
(2, 'student1', 'student1pass', 'student', 'student1@example.com', '2025-01-16 17:19:10'),
(10, 'admin', '123', 'admin', 'admin1@example.com', '2025-01-16 17:27:54'),
(11, 'root', '123', 'mentor', 'a@mail.co', '2025-02-27 14:30:56'),
(13, 'anoop', '123', 'student', NULL, '2025-02-27 14:52:56'),
(16, 'sudeep', '123', 'mentor', 'sudeep@gmail.com', '2025-02-27 15:10:11'),
(17, 'praju', '123', 'student', NULL, '2025-02-27 15:10:38');

-- --------------------------------------------------------

--
-- Table structure for table `weekly_questions`
--

CREATE TABLE `weekly_questions` (
  `question_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `response` text DEFAULT NULL,
  `generated_at` date NOT NULL,
  `sentiment` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `weekly_questions`
--

INSERT INTO `weekly_questions` (`question_id`, `student_id`, `question_text`, `response`, `generated_at`, `sentiment`) VALUES
(16, 2, 'Do you feel confident in your academic progress?', 'agree', '2025-02-27', 'Positive'),
(17, 2, 'Are you satisfied with your recent exam preparation?', 'disagree', '2025-02-27', 'Negative'),
(18, 2, 'Do you find your coursework manageable?', 'disagree', '2025-02-27', 'Negative'),
(19, 2, 'Have you been able to focus well while studying?', 'agree', '2025-02-27', 'Positive'),
(20, 2, 'Are you comfortable with the speed of teaching?', 'agree', '2025-02-27', 'Positive'),
(21, 2, 'Do you feel stressed about your assignments?', 'agree', '2025-02-27', 'Negative'),
(22, 2, 'Do you understand the concepts taught in class?', 'disagree', '2025-02-27', 'Negative'),
(23, 2, 'Have you been actively participating in class discussions?', 'disagree', '2025-02-27', 'Negative'),
(24, 2, 'Are you able to complete your homework on time?', 'agree', '2025-02-27', 'Positive'),
(25, 2, 'Do you feel prepared for your upcoming exams?', 'agree', '2025-02-27', 'Positive'),
(26, 2, 'Do you find group study sessions beneficial?', 'agree', '2025-02-27', 'Positive'),
(27, 2, 'Have you been able to revise previous lessons effectively?', 'agree', '2025-02-27', 'Positive'),
(28, 2, 'Do you feel your mentor provides good guidance?', 'agree', '2025-02-27', 'Positive'),
(29, 2, 'Are you managing your time efficiently for studies?', 'agree', '2025-02-27', 'Positive'),
(30, 2, 'Do you feel motivated to achieve your academic goals?', 'agree', '2025-02-27', 'Positive'),
(31, 2, 'Do you feel confident in your academic progress?', 'disagree', '2025-02-27', 'Negative'),
(32, 2, 'Are you satisfied with your recent exam preparation?', 'agree', '2025-02-27', 'Positive'),
(33, 2, 'Do you find your coursework manageable?', 'agree', '2025-02-27', 'Positive'),
(34, 2, 'Have you been able to focus well while studying?', 'agree', '2025-02-27', 'Positive'),
(35, 2, 'Are you comfortable with the speed of teaching?', 'disagree', '2025-02-27', 'Negative'),
(36, 2, 'Do you feel stressed about your assignments?', 'agree', '2025-02-27', 'Negative'),
(37, 2, 'Do you understand the concepts taught in class?', 'disagree', '2025-02-27', 'Negative'),
(38, 2, 'Have you been actively participating in class discussions?', 'disagree', '2025-02-27', 'Negative'),
(39, 2, 'Are you able to complete your homework on time?', 'agree', '2025-02-27', 'Positive'),
(40, 2, 'Do you feel prepared for your upcoming exams?', 'agree', '2025-02-27', 'Positive'),
(41, 2, 'Do you find group study sessions beneficial?', 'disagree', '2025-02-27', 'Negative'),
(42, 2, 'Have you been able to revise previous lessons effectively?', 'agree', '2025-02-27', 'Positive'),
(43, 2, 'Do you feel your mentor provides good guidance?', 'agree', '2025-02-27', 'Positive'),
(44, 2, 'Are you managing your time efficiently for studies?', 'disagree', '2025-02-27', 'Negative'),
(45, 2, 'Do you feel motivated to achieve your academic goals?', 'agree', '2025-02-27', 'Positive'),
(46, 2, 'Do you experience anxiety before exams?', 'agree', '2025-02-27', 'Negative'),
(47, 2, 'Overall Sentiment Result', 'Overall', '2025-02-27', 'Neutral'),
(48, 17, 'Do you feel confident in your academic progress?', 'agree', '2025-02-27', 'Positive'),
(49, 17, 'Are you satisfied with your recent exam preparation?', 'disagree', '2025-02-27', 'Negative'),
(50, 17, 'Do you find your coursework manageable?', 'disagree', '2025-02-27', 'Negative'),
(51, 17, 'Have you been able to focus well while studying?', 'agree', '2025-02-27', 'Positive'),
(52, 17, 'Are you comfortable with the speed of teaching?', 'agree', '2025-02-27', 'Positive'),
(53, 17, 'Do you feel stressed about your assignments?', 'disagree', '2025-02-27', 'Positive'),
(54, 17, 'Do you understand the concepts taught in class?', 'agree', '2025-02-27', 'Positive'),
(55, 17, 'Have you been actively participating in class discussions?', 'disagree', '2025-02-27', 'Negative'),
(56, 17, 'Are you able to complete your homework on time?', 'agree', '2025-02-27', 'Positive'),
(57, 17, 'Do you feel prepared for your upcoming exams?', 'agree', '2025-02-27', 'Positive'),
(58, 17, 'Do you find group study sessions beneficial?', 'agree', '2025-02-27', 'Positive'),
(59, 17, 'Have you been able to revise previous lessons effectively?', 'agree', '2025-02-27', 'Positive'),
(60, 17, 'Do you feel your mentor provides good guidance?', 'agree', '2025-02-27', 'Positive'),
(61, 17, 'Are you managing your time efficiently for studies?', 'agree', '2025-02-27', 'Positive'),
(62, 17, 'Do you feel motivated to achieve your academic goals?', 'agree', '2025-02-27', 'Positive'),
(63, 17, 'Do you experience anxiety before exams?', 'agree', '2025-02-27', 'Negative'),
(64, 17, 'Overall Sentiment Result', 'Overall', '2025-02-27', 'Positive');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `mentor_id` (`mentor_id`);

--
-- Indexes for table `meetings`
--
ALTER TABLE `meetings`
  ADD PRIMARY KEY (`meeting_id`),
  ADD KEY `mentor_id` (`mentor_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `mentors`
--
ALTER TABLE `mentors`
  ADD PRIMARY KEY (`mentor_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `mentor_id` (`mentor_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `weekly_questions`
--
ALTER TABLE `weekly_questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `meetings`
--
ALTER TABLE `meetings`
  MODIFY `meeting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mentors`
--
ALTER TABLE `mentors`
  MODIFY `mentor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `weekly_questions`
--
ALTER TABLE `weekly_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`mentor_id`) REFERENCES `mentors` (`mentor_id`);

--
-- Constraints for table `meetings`
--
ALTER TABLE `meetings`
  ADD CONSTRAINT `meetings_ibfk_1` FOREIGN KEY (`mentor_id`) REFERENCES `mentors` (`mentor_id`),
  ADD CONSTRAINT `meetings_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `mentors`
--
ALTER TABLE `mentors`
  ADD CONSTRAINT `mentors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`mentor_id`) REFERENCES `mentors` (`mentor_id`);

--
-- Constraints for table `weekly_questions`
--
ALTER TABLE `weekly_questions`
  ADD CONSTRAINT `weekly_questions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
