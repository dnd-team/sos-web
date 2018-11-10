-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Server version: 5.5.60-MariaDB
-- PHP Version: 7.1.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sos_prod`
--

-- --------------------------------------------------------

--
-- Table structure for table `Aushang`
--

CREATE TABLE `Aushang` (
  `id` int(11) NOT NULL,
  `token` longtext NOT NULL,
  `verified` int(2) NOT NULL,
  `schoolid` varchar(512) NOT NULL,
  `title` varchar(128) NOT NULL,
  `text` longtext NOT NULL,
  `image` varchar(255) NOT NULL,
  `action_type` int(1) NOT NULL,
  `action_url` longtext NOT NULL,
  `grades` longtext NOT NULL,
  `category` varchar(512) NOT NULL,
  `days` varchar(512) NOT NULL,
  `keep_at_top` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `random_token` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `AushangFiles`
--

CREATE TABLE `AushangFiles` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(512) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `uploaded` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Aushang_Files`
--

CREATE TABLE `Aushang_Files` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(512) NOT NULL,
  `aushang_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `BugReports`
--

CREATE TABLE `BugReports` (
  `id` int(11) NOT NULL,
  `token` mediumtext NOT NULL,
  `schoolid` varchar(512) NOT NULL,
  `name` varchar(512) NOT NULL,
  `desc` mediumtext NOT NULL,
  `link` mediumtext NOT NULL,
  `report_id` varchar(512) NOT NULL,
  `done` int(1) NOT NULL,
  `author` varchar(512) NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Cities`
--

CREATE TABLE `Cities` (
  `id` int(11) NOT NULL,
  `token` varchar(16) NOT NULL,
  `name` varchar(512) NOT NULL,
  `federal_state` varchar(512) NOT NULL,
  `country` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `City_Schools`
--

CREATE TABLE `City_Schools` (
  `id` int(11) NOT NULL,
  `token` varchar(16) NOT NULL,
  `city_id` int(11) NOT NULL,
  `school_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Consultations`
--

CREATE TABLE `Consultations` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(512) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `time` varchar(512) DEFAULT NULL,
  `ver` int(1) DEFAULT NULL,
  `name` varchar(512) NOT NULL,
  `email` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `CourseEntries`
--

CREATE TABLE `CourseEntries` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(255) NOT NULL,
  `token` mediumtext NOT NULL,
  `title` varchar(512) NOT NULL,
  `desc` mediumtext NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Course_Entries`
--

CREATE TABLE `Course_Entries` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(255) NOT NULL,
  `course_id` int(11) NOT NULL,
  `entry_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Course_Homework`
--

CREATE TABLE `Course_Homework` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(512) NOT NULL,
  `course_id` int(11) NOT NULL,
  `homework_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Course_Members`
--

CREATE TABLE `Course_Members` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(512) NOT NULL,
  `course_id` int(100) NOT NULL,
  `member_id` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Course_Tasks`
--

CREATE TABLE `Course_Tasks` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(512) NOT NULL,
  `course_id` int(12) NOT NULL,
  `task_id` int(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Devices`
--

CREATE TABLE `Devices` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(255) NOT NULL,
  `token` mediumtext NOT NULL,
  `device_token` mediumtext NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `FederalStates`
--

CREATE TABLE `FederalStates` (
  `id` int(11) NOT NULL,
  `token` varchar(16) NOT NULL,
  `name` varchar(512) NOT NULL,
  `country` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Grades`
--

CREATE TABLE `Grades` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(512) NOT NULL COMMENT 'Id for each school',
  `grade` varchar(50) NOT NULL,
  `grade_level` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Homework`
--

CREATE TABLE `Homework` (
  `id` int(11) NOT NULL,
  `token` mediumtext NOT NULL,
  `schoolid` varchar(512) NOT NULL,
  `title` varchar(255) NOT NULL,
  `desc` mediumtext NOT NULL,
  `course` varchar(512) NOT NULL,
  `expire_date` varchar(512) NOT NULL,
  `first_reminder` varchar(512) NOT NULL,
  `second_reminder` varchar(512) NOT NULL,
  `done` int(1) NOT NULL,
  `random_token` mediumtext NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `LCCourses`
--

CREATE TABLE `LCCourses` (
  `id` int(11) NOT NULL,
  `token` text NOT NULL,
  `schoolid` varchar(512) NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `course_id` varchar(512) NOT NULL,
  `grade` varchar(255) NOT NULL,
  `grade_level` int(11) NOT NULL,
  `teacher_id` varchar(255) NOT NULL,
  `course_name` varchar(512) NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `LCFiles`
--

CREATE TABLE `LCFiles` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(512) NOT NULL,
  `token` text NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `folder_token` text NOT NULL,
  `user_file_name` varchar(255) NOT NULL,
  `uploaded` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `LCTasks`
--

CREATE TABLE `LCTasks` (
  `id` int(11) NOT NULL,
  `token` mediumtext NOT NULL,
  `schoolid` varchar(512) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(512) NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `expire_date` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `LCUser`
--

CREATE TABLE `LCUser` (
  `id` int(11) NOT NULL,
  `token` mediumtext NOT NULL,
  `username` varchar(200) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `password` mediumtext NOT NULL,
  `email` varchar(250) NOT NULL,
  `ver_code` mediumtext NOT NULL,
  `verified` int(10) NOT NULL DEFAULT '0',
  `api_key` mediumtext NOT NULL,
  `hasLoggedIn` int(2) NOT NULL DEFAULT '0',
  `joinedSchool` int(11) NOT NULL DEFAULT '0',
  `teacher_id` varchar(255) NOT NULL,
  `schoolid` varchar(512) NOT NULL,
  `role` varchar(200) NOT NULL DEFAULT 'STUDENT',
  `grade` varchar(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `version` varchar(20) NOT NULL,
  `isBetaUser` int(11) NOT NULL,
  `isAlphaUser` int(11) NOT NULL,
  `hasVoted` tinyint(1) NOT NULL DEFAULT '0',
  `sos_points` int(3) NOT NULL DEFAULT '0',
  `voteLater` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Messages`
--

CREATE TABLE `Messages` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(255) NOT NULL,
  `message_title` varchar(255) NOT NULL,
  `message` mediumtext NOT NULL,
  `message_grade` varchar(255) NOT NULL,
  `for_devices` int(2) NOT NULL,
  `send` tinyint(1) NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Schools`
--

CREATE TABLE `Schools` (
  `id` int(254) NOT NULL,
  `token` varchar(16) NOT NULL,
  `school_name` varchar(512) NOT NULL,
  `school_type` varchar(512) NOT NULL,
  `schoolid` varchar(512) NOT NULL,
  `random_token` mediumtext NOT NULL,
  `count_courses` int(100) NOT NULL DEFAULT '0',
  `count_tasks` int(100) NOT NULL DEFAULT '0',
  `teacher_code` varchar(1) NOT NULL,
  `student_code` varchar(1) NOT NULL,
  `areHolidays` tinyint(1) NOT NULL,
  `timetable_student_url` text NOT NULL,
  `timetable_room_url` text NOT NULL,
  `timetable_teacher_url` text NOT NULL,
  `school_url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Task_Files`
--

CREATE TABLE `Task_Files` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(500) NOT NULL,
  `file_id` int(10) NOT NULL,
  `task_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Task_Owner`
--

CREATE TABLE `Task_Owner` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(512) NOT NULL,
  `user_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `TeacherUser`
--

CREATE TABLE `TeacherUser` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(120) DEFAULT NULL,
  `teacher_id` varchar(512) NOT NULL,
  `name` varchar(12) DEFAULT NULL,
  `surname` varchar(16) DEFAULT NULL,
  `lessons` varchar(59) DEFAULT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Teacher_Consultations`
--

CREATE TABLE `Teacher_Consultations` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `full` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `TimetableStudents`
--

CREATE TABLE `TimetableStudents` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(512) NOT NULL,
  `grade` varchar(10) NOT NULL,
  `jsonTimetable_a` mediumtext NOT NULL,
  `jsonTimetable_b` mediumtext NOT NULL,
  `timetableURL` varchar(512) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `TimetableTeacher`
--

CREATE TABLE `TimetableTeacher` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(512) NOT NULL,
  `teacher` varchar(512) NOT NULL,
  `jsonTimetable_a` mediumtext NOT NULL,
  `jsonTimetable_b` mediumtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `User_Aushang`
--

CREATE TABLE `User_Aushang` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(255) NOT NULL,
  `aushang_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `User_Devices`
--

CREATE TABLE `User_Devices` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `User_Homework`
--

CREATE TABLE `User_Homework` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(512) NOT NULL,
  `user_id` int(11) NOT NULL,
  `homework_id` int(11) NOT NULL,
  `accepted` int(1) NOT NULL DEFAULT '1',
  `isOwner` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `VPDescriptions`
--

CREATE TABLE `VPDescriptions` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `VPs`
--

CREATE TABLE `VPs` (
  `id` int(11) NOT NULL,
  `schoolid` varchar(512) NOT NULL,
  `daynumber` int(2) NOT NULL,
  `vp_day` date NOT NULL,
  `jsonDraftVP` text NOT NULL,
  `jsonVP` text NOT NULL,
  `vpURL` varchar(400) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Aushang`
--
ALTER TABLE `Aushang`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `AushangFiles`
--
ALTER TABLE `AushangFiles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Aushang_Files`
--
ALTER TABLE `Aushang_Files`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `BugReports`
--
ALTER TABLE `BugReports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Cities`
--
ALTER TABLE `Cities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `City_Schools`
--
ALTER TABLE `City_Schools`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Consultations`
--
ALTER TABLE `Consultations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `CourseEntries`
--
ALTER TABLE `CourseEntries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Course_Entries`
--
ALTER TABLE `Course_Entries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Course_Homework`
--
ALTER TABLE `Course_Homework`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Course_Members`
--
ALTER TABLE `Course_Members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Course_Tasks`
--
ALTER TABLE `Course_Tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Devices`
--
ALTER TABLE `Devices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `FederalStates`
--
ALTER TABLE `FederalStates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Grades`
--
ALTER TABLE `Grades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Homework`
--
ALTER TABLE `Homework`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `LCCourses`
--
ALTER TABLE `LCCourses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `LCFiles`
--
ALTER TABLE `LCFiles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `LCTasks`
--
ALTER TABLE `LCTasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `LCUser`
--
ALTER TABLE `LCUser`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Messages`
--
ALTER TABLE `Messages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `Schools`
--
ALTER TABLE `Schools`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Task_Files`
--
ALTER TABLE `Task_Files`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Task_Owner`
--
ALTER TABLE `Task_Owner`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `TeacherUser`
--
ALTER TABLE `TeacherUser`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Teacher_Consultations`
--
ALTER TABLE `Teacher_Consultations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `TimetableStudents`
--
ALTER TABLE `TimetableStudents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `TimetableTeacher`
--
ALTER TABLE `TimetableTeacher`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `User_Aushang`
--
ALTER TABLE `User_Aushang`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `User_Devices`
--
ALTER TABLE `User_Devices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `User_Homework`
--
ALTER TABLE `User_Homework`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `VPDescriptions`
--
ALTER TABLE `VPDescriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `VPs`
--
ALTER TABLE `VPs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Aushang`
--
ALTER TABLE `Aushang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `AushangFiles`
--
ALTER TABLE `AushangFiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Aushang_Files`
--
ALTER TABLE `Aushang_Files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `BugReports`
--
ALTER TABLE `BugReports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Cities`
--
ALTER TABLE `Cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `City_Schools`
--
ALTER TABLE `City_Schools`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Consultations`
--
ALTER TABLE `Consultations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `CourseEntries`
--
ALTER TABLE `CourseEntries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Course_Entries`
--
ALTER TABLE `Course_Entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Course_Homework`
--
ALTER TABLE `Course_Homework`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Course_Members`
--
ALTER TABLE `Course_Members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Course_Tasks`
--
ALTER TABLE `Course_Tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Devices`
--
ALTER TABLE `Devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `FederalStates`
--
ALTER TABLE `FederalStates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Grades`
--
ALTER TABLE `Grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Homework`
--
ALTER TABLE `Homework`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `LCCourses`
--
ALTER TABLE `LCCourses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `LCFiles`
--
ALTER TABLE `LCFiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `LCTasks`
--
ALTER TABLE `LCTasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `LCUser`
--
ALTER TABLE `LCUser`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Messages`
--
ALTER TABLE `Messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Schools`
--
ALTER TABLE `Schools`
  MODIFY `id` int(254) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Task_Files`
--
ALTER TABLE `Task_Files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Task_Owner`
--
ALTER TABLE `Task_Owner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TeacherUser`
--
ALTER TABLE `TeacherUser`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Teacher_Consultations`
--
ALTER TABLE `Teacher_Consultations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TimetableStudents`
--
ALTER TABLE `TimetableStudents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TimetableTeacher`
--
ALTER TABLE `TimetableTeacher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `User_Aushang`
--
ALTER TABLE `User_Aushang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `User_Devices`
--
ALTER TABLE `User_Devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `User_Homework`
--
ALTER TABLE `User_Homework`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `VPDescriptions`
--
ALTER TABLE `VPDescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `VPs`
--
ALTER TABLE `VPs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
