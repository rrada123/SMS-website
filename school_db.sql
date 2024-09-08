-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 31, 2024 at 08:25 AM
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
-- Database: `school_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_subject_to_student` (IN `p_student_name` VARCHAR(255), IN `p_subject_code` VARCHAR(10))   BEGIN
    DECLARE v_count INT;
    DECLARE v_subject_name VARCHAR(255);
    DECLARE v_teacher_name VARCHAR(255);
    DECLARE v_schedule VARCHAR(255);

    -- Check the current number of subjects assigned to the student
    SELECT COUNT(*) INTO v_count
    FROM classes
    WHERE student_name = p_student_name;

    -- If the number of subjects is less than 5
    IF v_count < 5 THEN
        -- Check if the subject is already assigned to the student
        IF EXISTS (
            SELECT 1
            FROM classes
            WHERE student_name = p_student_name AND code = p_subject_code
        ) THEN
            -- Update the existing subject assignment
            SELECT name, teacher, schedule
            INTO v_subject_name, v_teacher_name, v_schedule
            FROM subjects
            WHERE code = p_subject_code;

            UPDATE classes
            SET teacher = v_teacher_name,
                name = v_subject_name,
                schedule = v_schedule
            WHERE student_name = p_student_name AND code = p_subject_code;
        ELSE
            -- Insert the new subject assignment
            SELECT name, teacher, schedule
            INTO v_subject_name, v_teacher_name, v_schedule
            FROM subjects
            WHERE code = p_subject_code;

            INSERT INTO classes (student_name, teacher, name, code, schedule)
            VALUES (p_student_name, v_teacher_name, v_subject_name, p_subject_code, v_schedule);
        END IF;
    ELSE
        -- Raise an error if the student already has 5 subjects
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot add more than 5 subjects for a student.';
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `email`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `student_name` varchar(255) DEFAULT NULL,
  `teacher` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `schedule` varchar(255) DEFAULT NULL,
  `Grades` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `student_name`, `teacher`, `name`, `code`, `schedule`, `Grades`) VALUES
(24250001, 'John Doe', 'Atty R. Jordan', 'Business Law Obligations and Contracts with Real Property Laws', 'ELECT 102', '07:15 AM - 08:15 AM', 1.50),
(24250001, 'John Doe', 'Atty R. Jordan', 'Human and Physical Geography', 'GE 8', '10:44 AM - 11:44 AM', 3.00),
(24250001, 'John Doe', 'Billones Eddie', 'Introduction to My Profession and Ethics', 'IS 109', '07:44 AM - 09:44 AM', 2.00),
(24250001, 'John Doe', 'Cabansagan Joan Ruth', 'Real Estate Marketing and Brokerage', 'PS 103', '09:38 AM - 10:38 AM', 1.00),
(24250001, 'John Doe', 'Cabansagan Joan Ruth', 'Real Estate Economics', 'PS 106', '08:11 AM - 09:11 AM', 3.50),
(24250002, 'Christian Dela Cruz', 'Billones Eddie', 'Business Intelligence and Emerging Technologies', 'CA 102', '01:52 PM - 02:52 PM', 1.50),
(24250002, 'Christian Dela Cruz', 'Billones Eddie', 'Application Management', 'CC 106', '09:29 AM - 11:29 AM', 2.00),
(24250002, 'Christian Dela Cruz', 'Billones Eddie', 'Business Process Management', 'DM 103', '12:26 PM - 01:26 PM', 3.00),
(24250002, 'Christian Dela Cruz', 'Atty R. Jordan', 'Business Law Obligations and Contracts with Real Property Laws', 'ELECT 102', '07:15 AM - 08:15 AM', 2.00),
(24250002, 'Christian Dela Cruz', 'Atty R. Jordan', 'Human and Physical Geography', 'GE 8', '10:44 AM - 11:44 AM', 5.00),
(24250002, 'Christian Dela Cruz', 'Billones Eddie', 'Enterprise Architectures', 'IS 105', '08:33 AM - 09:33 AM', 0.00),
(24250002, 'Christian Dela Cruz', 'Billones Eddie', 'Introduction to My Profession and Ethics', 'IS 109', '07:44 AM - 09:44 AM', 0.00),
(24250002, 'Christian Dela Cruz', 'Cabansagan Joan Ruth', 'Real Estate Marketing and Brokerage', 'PS 103', '09:38 AM - 10:38 AM', 0.00),
(24250002, 'Christian Dela Cruz', 'Cabansagan Joan Ruth', 'Real Estate Economics', 'PS 106', '08:11 AM - 09:11 AM', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `age` int(11) NOT NULL,
  `birthday` date NOT NULL,
  `place_of_birth` varchar(255) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `citizenship` varchar(255) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `present_address` varchar(255) NOT NULL,
  `permanent_address` varchar(255) NOT NULL,
  `mothers_name` varchar(255) NOT NULL,
  `mothers_occupation` varchar(255) NOT NULL,
  `mothers_contact` varchar(20) NOT NULL,
  `fathers_name` varchar(255) NOT NULL,
  `fathers_contact` varchar(20) NOT NULL,
  `fathers_occupation` varchar(255) NOT NULL,
  `year_level` int(11) NOT NULL,
  `section` int(11) DEFAULT NULL,
  `course` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `first_name`, `middle_name`, `last_name`, `age`, `birthday`, `place_of_birth`, `gender`, `citizenship`, `contact_number`, `email_address`, `present_address`, `permanent_address`, `mothers_name`, `mothers_occupation`, `mothers_contact`, `fathers_name`, `fathers_contact`, `fathers_occupation`, `year_level`, `section`, `course`, `status`) VALUES
(24250001, 'John', 'P ', 'Doe', 25, '1997-08-28', 'Iloilo', 'Male', 'Filipino', '09185307244', 'jpd@gmail.com', 'ILoilo', 'Iloilo', 'Mother test', 'laborer', '0912345678', 'Father test', '0912345678', 'office', 2, 4, '3', 'Enrolled'),
(24250002, 'Christian', 'D', 'Dela Cruz', 21, '2002-06-11', 'Iloilo', 'Male', 'Filipino', '09185307244', 'cdd@gmail.com', 'ILoilo', 'Iloilo', 'Mother test', '', '', 'Father test', '', '', 0, 2, 'BS GENED', 'Pending'),
(24250005, 'mary', 'H', 'Lastico', 22, '2002-08-28', 'Iloilo', 'Female', 'Filipino', '09185307244', 'MHL@gmail.com', 'ILoilo', 'Iloilo', 'Mother test', '', '', 'Father test', '', '', 0, 1, 'BS CRIM', 'Drop Out');

--
-- Triggers `students`
--
DELIMITER $$
CREATE TRIGGER `after_student_insert` AFTER INSERT ON `students` FOR EACH ROW BEGIN
    INSERT INTO classes (id)
    VALUES (NEW.id);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_student_update` AFTER UPDATE ON `students` FOR EACH ROW BEGIN
    UPDATE classes
    SET id = NEW.id
    WHERE id = OLD.id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(50) DEFAULT NULL,
  `teacher` varchar(255) DEFAULT NULL,
  `schedule` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `code`, `name`, `description`, `teacher`, `schedule`) VALUES
(3, 'GE 8', 'Human and Physical Geography', 'IS', 'Atty R. Jordan', '10:44 AM - 11:44 AM'),
(4, 'ELECT 102', 'Business Law Obligations and Contracts with Real Property Laws', 'IS', 'Atty R. Jordan', '07:15 AM - 08:15 AM'),
(5, 'PS 106', 'Real Estate Economics', 'IS', 'Cabansagan Joan Ruth', '08:11 AM - 09:11 AM'),
(6, 'PS 103', 'Real Estate Marketing and Brokerage', 'QUAMET', 'Cabansagan Joan Ruth', '09:38 AM - 10:38 AM'),
(7, 'IS 109', 'Introduction to My Profession and Ethics', 'IS', 'Billones Eddie', '07:44 AM - 09:44 AM'),
(8, 'IS 105', 'Enterprise Architectures', 'IS', 'Billones Eddie', '08:33 AM - 09:33 AM'),
(9, 'DM 103', 'Business Process Management', 'IS', 'Billones Eddie', '12:26 PM - 01:26 PM'),
(10, 'CC 106', 'Application Management', 'IS', 'Billones Eddie', '09:29 AM - 11:29 AM'),
(11, 'CA 102', 'Business Intelligence and Emerging Technologies', 'IS', 'Billones Eddie', '01:52 PM - 02:52 PM'),
(12, 'ADV 05', 'Customer Relationship Management', 'IS', 'Billones Eddie', '02:02 PM - 04:02 PM'),
(13, 'ADV 04', 'Enterprise Resource Planning', 'IS', 'Billones Eddie', '09:40 AM - 10:40 AM'),
(15, 'ADV 02', 'Project Feasibility Study', 'REM', 'Billones Eddie', '09:41 AM - 10:41 AM'),
(16, 'REM', 'Appraisal and Assessment in the Government Sector', 'REM', 'Billones Eddie', '08:52 AM - 09:52 AM'),
(17, 'MEC 101', 'Business Ethics', 'REM', 'Billones Eddie', '12:14 PM - 02:14 PM'),
(19, 'GE 1', 'Understanding the Self', 'IS', 'Ambacan Princess Joy M', '03:53 PM - 04:53 PM'),
(20, 'GE 8', 'Human and Physical Geography', 'IS', 'Atty R. Jordan', '11:04 AM - 12:04 PM'),
(21, 'ELECT 102', 'Business Law Obligations and Contracts with Real Property Laws', 'IS', 'Atty R. Jordan', '12:59 PM - 01:59 PM'),
(30, 'ADV 04', 'Enterprise Resource Planning', 'IS', 'Billones Eddie', '11:23 AM - 01:23 PM'),
(32, 'ADV 02', 'Project Feasibility Study', 'REM', 'Billones Eddie', '09:31 AM - 11:31 AM'),
(33, 'REM', 'Appraisal and Assessment in the Government Sector', 'REM', 'Billones Eddie', '01:10 PM - 02:10 PM'),
(34, 'MEC 101', 'Business Ethics', 'REM', 'Billones Eddie', '09:20 AM - 11:20 AM'),
(39, 'ADV 02', 'Human Computer Interaction', 'IS', 'Billones Eddie', '02:30 PM - 04:30 PM'),
(45, 'FIL 1', 'rada', 'IS', 'Billones Eddie', '10:19 AM - 11:19 AM'),
(46, 'FIL 1', 'Sayko Sosyolingwistika na Pagsulat sa Wikang Filipino', 'IS', 'test', '10:19 AM - 11:19 AM');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`,`code`),
  ADD UNIQUE KEY `unique_subject_per_student` (`student_name`,`code`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`,`middle_name`,`birthday`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24250015;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
