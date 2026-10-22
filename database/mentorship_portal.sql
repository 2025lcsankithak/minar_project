-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: localhost    Database: mentorship_portal
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `mentorship_portal`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `mentorship_portal` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `mentorship_portal`;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mentor_id` int(11) NOT NULL,
  `title` varchar(120) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `mentor_id` (`mentor_id`,`created_at`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`mentor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_requests`
--

DROP TABLE IF EXISTS `course_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `semester` tinyint(4) NOT NULL DEFAULT 5,
  `total_credits` tinyint(4) NOT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_semester` (`student_id`,`semester`),
  CONSTRAINT `course_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_requests`
--

LOCK TABLES `course_requests` WRITE;
/*!40000 ALTER TABLE `course_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `credits` tinyint(4) NOT NULL,
  `semester` tinyint(4) NOT NULL,
  `type` enum('Core','Open Elective') NOT NULL DEFAULT 'Core',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,'CS501','Advanced Algorithms',3,5,'Core'),(2,'CS502','Database Management Systems',3,5,'Core'),(3,'CS503','Operating Systems',3,5,'Core'),(4,'CS504','Software Engineering',3,5,'Core'),(5,'OE501','Introduction to AI',3,5,'Open Elective'),(6,'OE502','Cloud Computing',3,5,'Open Elective');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meetings`
--

DROP TABLE IF EXISTS `meetings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meetings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `availability_id` int(11) DEFAULT NULL,
  `student_id` int(11) NOT NULL,
  `mentor_id` int(11) NOT NULL,
  `meeting_at` datetime NOT NULL,
  `mode` enum('Online','In person') NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','approved','confirmed','rejected') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `mentor_id` (`mentor_id`),
  KEY `availability_id` (`availability_id`,`status`),
  CONSTRAINT `fk_meetings_availability` FOREIGN KEY (`availability_id`) REFERENCES `mentor_availability` (`id`) ON DELETE SET NULL,
  CONSTRAINT `meetings_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `meetings_ibfk_2` FOREIGN KEY (`mentor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meetings`
--

LOCK TABLES `meetings` WRITE;
/*!40000 ALTER TABLE `meetings` DISABLE KEYS */;
INSERT INTO `meetings` VALUES (1,1,8,4,'2026-10-24 10:30:00','In person','About Parents-teacher meeting','pending');
/*!40000 ALTER TABLE `meetings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mentor_assignments`
--

DROP TABLE IF EXISTS `mentor_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mentor_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `mentor_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id` (`student_id`),
  KEY `mentor_id` (`mentor_id`),
  CONSTRAINT `mentor_assignments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mentor_assignments_ibfk_2` FOREIGN KEY (`mentor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mentor_assignments`
--

LOCK TABLES `mentor_assignments` WRITE;
/*!40000 ALTER TABLE `mentor_assignments` DISABLE KEYS */;
INSERT INTO `mentor_assignments` VALUES (3,7,6),(4,8,4);
/*!40000 ALTER TABLE `mentor_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mentor_availability`
--

DROP TABLE IF EXISTS `mentor_availability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mentor_availability` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mentor_id` int(11) NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `mode` enum('Online','In person') NOT NULL DEFAULT 'Online',
  `description` varchar(500) NOT NULL DEFAULT '',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `mentor_id` (`mentor_id`,`starts_at`),
  CONSTRAINT `mentor_availability_ibfk_1` FOREIGN KEY (`mentor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `CONSTRAINT_1` CHECK (`ends_at` > `starts_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mentor_availability`
--

LOCK TABLES `mentor_availability` WRITE;
/*!40000 ALTER TABLE `mentor_availability` DISABLE KEYS */;
INSERT INTO `mentor_availability` VALUES (1,4,'2026-10-24 10:30:00','2026-10-24 16:00:00','In person','',1,'2026-10-22 16:30:35');
/*!40000 ALTER TABLE `mentor_availability` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `request_courses`
--

DROP TABLE IF EXISTS `request_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `request_courses` (
  `request_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  PRIMARY KEY (`request_id`,`course_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `request_courses_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `course_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `request_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `request_courses`
--

LOCK TABLES `request_courses` WRITE;
/*!40000 ALTER TABLE `request_courses` DISABLE KEYS */;
/*!40000 ALTER TABLE `request_courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `user_id` int(11) NOT NULL,
  `attendance` decimal(5,2) NOT NULL DEFAULT 0.00,
  `cgpa` decimal(3,2) NOT NULL DEFAULT 0.00,
  `completed_credits` smallint(5) unsigned NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `CONSTRAINT_1` CHECK (`attendance` >= 0 and `attendance` <= 100),
  CONSTRAINT `CONSTRAINT_2` CHECK (`cgpa` >= 0 and `cgpa` <= 10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (7,88.00,8.40,72,'2026-10-22 15:42:01'),(8,94.50,9.10,78,'2026-10-22 15:42:01');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` enum('admin','mentor','student') NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `section` varchar(20) DEFAULT NULL,
  `semester` tinyint(4) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','System Administrator','admin','admin@minar.edu','$2y$10$tAztD8zO236L5w3vVph7QeB4V0VUJCpGDCXmO7pazyKnFbn9Nzpc6','Administration',NULL,NULL,NULL,'2026-10-11 06:55:03'),(2,'mentor','Adithi','adithi','adithi@gmail.com','$2y$10$vAXCe/khiQ2rlo/0XPK36.fwfgvxRyQ4PiVIxw/NvsEQ6pSnucwk.','CS','B',5,NULL,'2026-10-11 06:56:41'),(3,'mentor','Adii','adii','adii@gmail.com','$2y$10$/m5qHYPe3YvbPrioLqsr7.1k6VPna.WI2RkSzVdlCPzbj/EsiMieK','CS','A',5,NULL,'2026-10-11 06:57:35'),(4,'mentor','m3','m3.mentor','m3@gmain.com','$2y$10$Gv56ks4mAPHi3ydH/f4Hre4k58VfPun.KSlb3cpbyjeNBBB4Umdju','CS','B',5,NULL,'2026-10-11 06:59:15'),(6,'mentor','Ashwi','ashuu','ashuu@gmain.com','$2y$10$vRUe4KnUqi/Qj7nxdLAF8ua9TX3HXqA4DOcCPfJdWGjkY6A0Y29xW','CS','A',5,NULL,'2026-10-11 07:01:06'),(7,'student','Ankitha K','anki','anki@gmail.com','$2y$10$DFeLdiDZHZWXlQM4JyQ0XuhS.UpPnqlYtTSBieiOO2dOk/weGGwE.','CS','A',5,'2005-11-30','2026-10-11 07:05:25'),(8,'student','Ankitha Achar','ankitha.achar','ankitha@gmail.com','$2y$10$bwj724lr/rLKXLk5kEWFaeVvGcYG/.zM9t6V6aYEWmq4.5znrLH3K','CS','B',5,NULL,'2026-10-11 12:42:27');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'mentorship_portal'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-22 22:10:19
