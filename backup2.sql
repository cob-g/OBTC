-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: 10dayschallenge
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `client_checkins`
--

DROP TABLE IF EXISTS `client_checkins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_checkins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL,
  `coach_user_id` int(10) unsigned NOT NULL,
  `day_number` tinyint(3) unsigned NOT NULL,
  `weight_lbs` decimal(6,2) NOT NULL,
  `recorded_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_client_checkins_day` (`client_id`,`day_number`),
  KEY `idx_client_checkins_client_id` (`client_id`),
  KEY `idx_client_checkins_coach_user_id` (`coach_user_id`),
  CONSTRAINT `fk_client_checkins_client_id` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_client_checkins_coach_user_id` FOREIGN KEY (`coach_user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_checkins`
--

LOCK TABLES `client_checkins` WRITE;
/*!40000 ALTER TABLE `client_checkins` DISABLE KEYS */;
INSERT INTO `client_checkins` VALUES (1,1,2,1,204.00,'2026-01-26 20:14:56','2026-01-26 12:14:36');
/*!40000 ALTER TABLE `client_checkins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `coach_user_id` int(10) unsigned NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `age` tinyint(3) unsigned NOT NULL,
  `height_ft` tinyint(3) unsigned NOT NULL,
  `height_in` tinyint(3) unsigned NOT NULL,
  `start_weight_lbs` decimal(6,2) NOT NULL,
  `waistline_in` decimal(6,2) NOT NULL,
  `bmi` decimal(6,2) NOT NULL,
  `bmi_category` varchar(32) NOT NULL,
  `front_photo_path` varchar(255) NOT NULL,
  `side_photo_path` varchar(255) NOT NULL,
  `day10_front_photo_path` varchar(255) DEFAULT NULL,
  `day10_side_photo_path` varchar(255) DEFAULT NULL,
  `challenge_start_date` date DEFAULT NULL,
  `registered_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_clients_coach_user_id` (`coach_user_id`),
  CONSTRAINT `fk_clients_coach_user_id` FOREIGN KEY (`coach_user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (1,2,'billy james','male',21,5,4,205.00,65.00,35.18,'Extremely Obese','C:\\xampp\\htdocs\\10dayschallenge\\storage/uploads/pre_registration/192094306318731c4ac6af3a\\front.png','C:\\xampp\\htdocs\\10dayschallenge\\storage/uploads/pre_registration/192094306318731c4ac6af3a\\side.png',NULL,NULL,'2026-01-26','2026-01-26 20:14:13','2026-01-26 12:14:13','2026-01-26 12:14:13');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coach_challenge_participants`
--

DROP TABLE IF EXISTS `coach_challenge_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coach_challenge_participants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `coach_challenge_id` int(10) unsigned NOT NULL,
  `coach_user_id` int(10) unsigned NOT NULL,
  `height_ft` tinyint(3) unsigned NOT NULL,
  `height_in` tinyint(3) unsigned NOT NULL,
  `start_weight_lbs` decimal(6,2) NOT NULL,
  `bmi` decimal(6,2) NOT NULL,
  `bmi_category` varchar(32) NOT NULL,
  `registered_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_coach_challenge_participant` (`coach_challenge_id`,`coach_user_id`),
  KEY `idx_coach_participants_challenge` (`coach_challenge_id`),
  KEY `idx_coach_participants_coach` (`coach_user_id`),
  CONSTRAINT `fk_coach_participants_challenge` FOREIGN KEY (`coach_challenge_id`) REFERENCES `coach_challenges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_coach_participants_user` FOREIGN KEY (`coach_user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coach_challenge_participants`
--

LOCK TABLES `coach_challenge_participants` WRITE;
/*!40000 ALTER TABLE `coach_challenge_participants` DISABLE KEYS */;
/*!40000 ALTER TABLE `coach_challenge_participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coach_challenges`
--

DROP TABLE IF EXISTS `coach_challenges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coach_challenges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `duration_days` tinyint(3) unsigned NOT NULL DEFAULT 10,
  `status` enum('active','completed') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_coach_challenges_status_start` (`status`,`start_date`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coach_challenges`
--

LOCK TABLES `coach_challenges` WRITE;
/*!40000 ALTER TABLE `coach_challenges` DISABLE KEYS */;
INSERT INTO `coach_challenges` VALUES (1,'Coach Challenge (2026-01-26)','2026-01-26',10,'active','2026-01-26 12:11:49','2026-01-26 12:11:49');
/*!40000 ALTER TABLE `coach_challenges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coach_checkins`
--

DROP TABLE IF EXISTS `coach_checkins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coach_checkins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `coach_challenge_id` int(10) unsigned NOT NULL,
  `coach_user_id` int(10) unsigned NOT NULL,
  `day_number` tinyint(3) unsigned NOT NULL,
  `weight_lbs` decimal(6,2) NOT NULL,
  `recorded_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_coach_checkins_day` (`coach_challenge_id`,`coach_user_id`,`day_number`),
  KEY `idx_coach_checkins_challenge` (`coach_challenge_id`),
  KEY `idx_coach_checkins_coach` (`coach_user_id`),
  CONSTRAINT `fk_coach_checkins_challenge` FOREIGN KEY (`coach_challenge_id`) REFERENCES `coach_challenges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_coach_checkins_user` FOREIGN KEY (`coach_user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coach_checkins`
--

LOCK TABLES `coach_checkins` WRITE;
/*!40000 ALTER TABLE `coach_checkins` DISABLE KEYS */;
/*!40000 ALTER TABLE `coach_checkins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consent_logs`
--

DROP TABLE IF EXISTS `consent_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consent_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL,
  `coach_user_id` int(10) unsigned NOT NULL,
  `consent_text` text NOT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_consent_logs_client_id` (`client_id`),
  KEY `idx_consent_logs_coach_user_id` (`coach_user_id`),
  CONSTRAINT `fk_consent_logs_client_id` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_consent_logs_coach_user_id` FOREIGN KEY (`coach_user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consent_logs`
--

LOCK TABLES `consent_logs` WRITE;
/*!40000 ALTER TABLE `consent_logs` DISABLE KEYS */;
INSERT INTO `consent_logs` VALUES (1,1,2,'I confirm that the client has provided consent for the collection and processing of their personal and health-related data in accordance with the Privacy Policy.','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0','2026-01-26 12:14:13');
/*!40000 ALTER TABLE `consent_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `role` enum('coach','admin') NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin@gmail.com','admin','$2y$10$7M.4Q4zFWuuUcI5UKxZwSeseL9Q3acMdXDTfBMlLKjbtQOScv.OWW',1,'2026-01-26 11:57:54','2026-01-26 11:57:54'),(2,'COACH JACOB','jacob@gmail.com','coach','$2y$10$0ET9B3272.QitR/sBx5H3u/4EhdkbK3P3uFxT93TqXrIVVldl3gvq',1,'2026-01-26 12:11:33','2026-01-26 12:11:33'),(3,'coach2','coach2@gmail.com','coach','$2y$10$cp8Rz5K1EYo8P1IUmu2OvOvohvLhgEZgENcaW/50ddOlDg1baUe3i',1,'2026-01-26 13:32:15','2026-01-26 13:32:15');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-27 19:41:59
