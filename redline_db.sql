/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.6-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: redline_db
-- ------------------------------------------------------
-- Server version	11.8.6-MariaDB-0+deb13u1 from Debian

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `cameras`
--

DROP TABLE IF EXISTS `cameras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cameras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `stream_url` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'inactive',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cameras`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cameras` WRITE;
/*!40000 ALTER TABLE `cameras` DISABLE KEYS */;
INSERT INTO `cameras` VALUES
(1,'Kamera 1 - Masuk','Gerbang Utama','http://192.168.43.206:81/stream','active'),
(2,'Kamera 2 - Keluar','Gerbang Belakang','http://192.168.43.206:81/stream','active');
/*!40000 ALTER TABLE `cameras` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `speed` int(11) DEFAULT NULL,
  `plate` varchar(20) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `mqtt_config`
--

DROP TABLE IF EXISTS `mqtt_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mqtt_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `broker` varchar(150) DEFAULT 'broker.emqx.io',
  `port` int(11) DEFAULT 1883,
  `topic` varchar(100) DEFAULT 'redline/speed',
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mqtt_config`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `mqtt_config` WRITE;
/*!40000 ALTER TABLE `mqtt_config` DISABLE KEYS */;
INSERT INTO `mqtt_config` VALUES
(1,'broker.emqx.io',1883,'redline/speed',NULL,NULL);
/*!40000 ALTER TABLE `mqtt_config` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES
(1,'speed_limit','60');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `speed_logs`
--

DROP TABLE IF EXISTS `speed_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `speed_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device` varchar(50) DEFAULT 'REDLINE_01',
  `speed` decimal(6,2) NOT NULL,
  `status` enum('safe','violation') NOT NULL DEFAULT 'safe',
  `plate` varchar(20) DEFAULT NULL,
  `photo_url` varchar(500) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `speed_logs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `speed_logs` WRITE;
/*!40000 ALTER TABLE `speed_logs` DISABLE KEYS */;
INSERT INTO `speed_logs` VALUES
(1,'REDLINE_01',50.50,'violation',NULL,NULL,NULL,'2026-05-09 22:33:57'),
(2,'REDLINE_01',55.20,'violation',NULL,NULL,NULL,'2026-05-09 22:34:39'),
(3,'REDLINE_01',1.25,'violation',NULL,NULL,NULL,'2026-05-10 14:58:34'),
(4,'REDLINE_01',1.25,'violation',NULL,NULL,NULL,'2026-05-10 14:59:25'),
(5,'REDLINE_01',1.50,'violation',NULL,NULL,NULL,'2026-05-10 16:26:39'),
(6,'REDLINE_01',1.50,'violation',NULL,NULL,NULL,'2026-05-10 16:26:55'),
(7,'REDLINE_01',1.85,'violation',NULL,NULL,NULL,'2026-05-10 19:05:25'),
(8,'REDLINE_01',1.25,'violation',NULL,NULL,NULL,'2026-05-10 20:30:17'),
(9,'REDLINE_01',5.66,'violation',NULL,NULL,NULL,'2026-05-10 20:49:33'),
(10,'REDLINE_01',1.33,'violation',NULL,NULL,NULL,'2026-05-10 20:49:40'),
(11,'REDLINE_01',1.67,'violation',NULL,NULL,NULL,'2026-05-10 20:50:13'),
(12,'REDLINE_01',2.10,'violation',NULL,NULL,NULL,'2026-05-10 20:50:17'),
(13,'REDLINE_01',1.59,'violation',NULL,NULL,NULL,'2026-05-10 20:50:23'),
(14,'REDLINE_01',1.26,'violation',NULL,NULL,NULL,'2026-05-10 20:52:14'),
(15,'REDLINE_01',1.31,'violation',NULL,NULL,NULL,'2026-05-10 20:52:18'),
(16,'REDLINE_01',3.03,'violation',NULL,NULL,NULL,'2026-05-11 08:02:11'),
(17,'REDLINE_01',1.53,'violation',NULL,NULL,NULL,'2026-05-12 09:50:07'),
(18,'REDLINE_01',1.02,'violation',NULL,NULL,NULL,'2026-05-12 09:51:19'),
(19,'REDLINE_01',2.13,'violation',NULL,NULL,NULL,'2026-05-12 09:55:35'),
(20,'REDLINE_01',1.02,'violation',NULL,NULL,NULL,'2026-05-12 09:55:40'),
(21,'REDLINE_01',3.23,'violation',NULL,NULL,NULL,'2026-05-12 09:55:43'),
(22,'REDLINE_01',2.02,'violation',NULL,NULL,NULL,'2026-05-12 10:22:36'),
(23,'REDLINE_01',2.58,'violation',NULL,NULL,NULL,'2026-05-12 10:23:43'),
(24,'REDLINE_01',2.48,'violation',NULL,NULL,NULL,'2026-05-12 10:24:10'),
(25,'REDLINE_01',5.00,'violation',NULL,NULL,NULL,'2026-05-12 10:24:22'),
(26,'REDLINE_01',5.00,'violation',NULL,NULL,NULL,'2026-05-12 10:25:17'),
(27,'REDLINE_01',4.96,'violation',NULL,NULL,NULL,'2026-05-12 10:25:29'),
(28,'REDLINE_01',5.00,'violation',NULL,NULL,NULL,'2026-05-12 10:25:33'),
(29,'REDLINE_01',2.08,'violation',NULL,NULL,NULL,'2026-05-12 10:26:20'),
(30,'REDLINE_01',2.59,'violation',NULL,NULL,NULL,'2026-05-12 10:26:26'),
(31,'REDLINE_01',1.08,'violation',NULL,NULL,NULL,'2026-05-12 10:28:27'),
(32,'REDLINE_01',5.00,'violation',NULL,NULL,NULL,'2026-05-12 10:28:32'),
(33,'REDLINE_01',5.00,'violation',NULL,NULL,NULL,'2026-05-12 10:28:36'),
(34,'REDLINE_01',2.59,'violation',NULL,NULL,NULL,'2026-05-12 10:29:13');
/*!40000 ALTER TABLE `speed_logs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `telegram` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `address` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `telegram_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(4,'admin',NULL,NULL,'$2y$12$HCfqXUzt6NlpvtzNG/3reOZE72TuZRQ3C/eSak/Vv0i5v/ChmVzVO','admin',NULL,'2026-05-09 15:50:24',NULL,NULL,1,'2026-05-09 22:50:24'),
(5,'redline','redline@gmail.com','083193302006','$2y$12$pcsudcgSAKYfECe.xFtEE.xIfKVie0zWgwJ0r1HpRqc3vnvt6U66a','admin','7202928311','2026-05-09 16:19:34',NULL,NULL,1,'2026-05-10 14:32:16'),
(6,'syifa','syifa@gmail.com',NULL,'$2y$12$NoLYFbzqFHqhtt9ZscEBw.YtNTuFG15nrADwtsUMAL1R4RIXysII2','admin',NULL,'2026-05-13 01:01:30',NULL,NULL,1,'2026-05-13 08:01:30'),
(7,'ELSYIFA','ELSYIFA@gmail.com',NULL,'$2y$12$5vCYqJ.Sk..iF7LVulptKuGNTUMPaDxhfWU6OODf41Ut1cUY1D/.y','admin',NULL,'2026-05-13 02:36:49',NULL,NULL,1,'2026-05-13 09:36:49');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-06-21 15:45:46
