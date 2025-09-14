/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.7.2-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: shakura
-- ------------------------------------------------------
-- Server version	11.4.4-MariaDB

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
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parcel_id` int(11) DEFAULT NULL,
  `invoice_number` varchar(30) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `invoice_date` datetime DEFAULT current_timestamp(),
  `pdf_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parcel_id` (`parcel_id`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`parcel_id`) REFERENCES `parcels` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES
(6,1,'INV00001',420.00,'2025-05-21 11:48:04',NULL),
(7,2,'INV00002',576.00,'2025-05-21 22:23:41',NULL);
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parcel_logs`
--

DROP TABLE IF EXISTS `parcel_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `parcel_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parcel_id` int(11) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `gps_lat` double DEFAULT NULL,
  `gps_long` double DEFAULT NULL,
  `log_type` enum('manual','rfid','gps') DEFAULT 'manual',
  `log_time` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `parcel_id` (`parcel_id`),
  CONSTRAINT `parcel_logs_ibfk_1` FOREIGN KEY (`parcel_id`) REFERENCES `parcels` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parcel_logs`
--

LOCK TABLES `parcel_logs` WRITE;
/*!40000 ALTER TABLE `parcel_logs` DISABLE KEYS */;
INSERT INTO `parcel_logs` VALUES
(6,1,'We Have Your Package','','',NULL,NULL,'manual','2025-05-21 12:00:53'),
(7,1,'On the Way','','',NULL,NULL,'manual','2025-05-21 12:12:58'),
(8,1,'Out for Delivery','Kiambu','RFID Checkpoint (GPS: -1.2872538,36.8372803)',NULL,NULL,'rfid','2025-05-21 12:19:39'),
(9,2,'We Have Your Package','Kiambu','RFID Checkpoint (GPS: -1.2524209,36.9275152)',NULL,NULL,'rfid','2025-05-21 22:25:29');
/*!40000 ALTER TABLE `parcel_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parcels`
--

DROP TABLE IF EXISTS `parcels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `parcels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tracking_number` varchar(30) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_name` varchar(100) DEFAULT NULL,
  `receiver_phone` varchar(20) DEFAULT NULL,
  `dispatch_origin` enum('US','UK','China','Dubai','Netherlands') NOT NULL,
  `delivery_country` enum('Kenya','Uganda','Tanzania') NOT NULL,
  `actual_weight` float NOT NULL,
  `length_cm` float NOT NULL,
  `width_cm` float NOT NULL,
  `height_cm` float NOT NULL,
  `volumetric_weight` float NOT NULL,
  `chargeable_weight` float NOT NULL,
  `fragile` enum('Yes','No') DEFAULT 'No',
  `price_per_kg` float NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `assigned_staff` int(11) DEFAULT NULL,
  `status` enum('Label Created','We Have Your Package','On the Way','Out for Delivery','Delivered') NOT NULL DEFAULT 'Label Created',
  `created_at` datetime DEFAULT current_timestamp(),
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tracking_number` (`tracking_number`),
  KEY `sender_id` (`sender_id`),
  KEY `assigned_staff` (`assigned_staff`),
  CONSTRAINT `parcels_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `parcels_ibfk_2` FOREIGN KEY (`assigned_staff`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parcels`
--

LOCK TABLES `parcels` WRITE;
/*!40000 ALTER TABLE `parcels` DISABLE KEYS */;
INSERT INTO `parcels` VALUES
(1,'TRK682d8914ba48e',2,'Raymond Njoroge','0721436541','US','Tanzania',20,32,14,11,0.821333,20,'Yes',16,420.00,3,'Out for Delivery','2025-05-21 11:04:36',NULL),
(2,'TRK682e282b5a563',2,'David','0726955034','US','Uganda',34,56,43,32,12.8427,34,'Yes',14,576.00,3,'We Have Your Package','2025-05-21 22:23:23',NULL);
/*!40000 ALTER TABLE `parcels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pricing`
--

DROP TABLE IF EXISTS `pricing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pricing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `per_kg` decimal(8,2) DEFAULT NULL,
  `per_cubic` decimal(8,2) DEFAULT NULL,
  `fragile_surcharge` decimal(8,2) DEFAULT NULL,
  `effective_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pricing`
--

LOCK TABLES `pricing` WRITE;
/*!40000 ALTER TABLE `pricing` DISABLE KEYS */;
/*!40000 ALTER TABLE `pricing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quotations`
--

DROP TABLE IF EXISTS `quotations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `quotations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `from_country` varchar(40) DEFAULT NULL,
  `from_county` varchar(40) DEFAULT NULL,
  `from_city` varchar(60) DEFAULT NULL,
  `to_country` varchar(40) DEFAULT NULL,
  `to_county` varchar(40) DEFAULT NULL,
  `to_city` varchar(60) DEFAULT NULL,
  `pickup_date` date DEFAULT NULL,
  `drop_type` varchar(30) DEFAULT NULL,
  `carrier` varchar(30) DEFAULT NULL,
  `item_desc` varchar(100) DEFAULT NULL,
  `packaging` varchar(30) DEFAULT NULL,
  `weight` float DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `volume` float DEFAULT NULL,
  `condition` varchar(15) DEFAULT NULL,
  `hazard` varchar(10) DEFAULT NULL,
  `alcohol` varchar(10) DEFAULT NULL,
  `residence` tinyint(4) DEFAULT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `requested_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quotations`
--

LOCK TABLES `quotations` WRITE;
/*!40000 ALTER TABLE `quotations` DISABLE KEYS */;
/*!40000 ALTER TABLE `quotations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_logs`
--

DROP TABLE IF EXISTS `system_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `action_time` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_logs`
--

LOCK TABLES `system_logs` WRITE;
/*!40000 ALTER TABLE `system_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','staff','customer') NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `address_line1` varchar(255) DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'admin','$2y$10$ChzcjcSV5Wgeyn3OLkbO8.rY0TYPy6Kuz4A8Vh5WKE2cBh0wGs86a','admin@gmail.com','admin','admin','071234567','2025-05-20 20:44:08',NULL,NULL,NULL,NULL,NULL),
(2,'john','$2y$10$Mz/V7062MRXvfMQalPC1P.Mmjompa9nl.dX0kC2kOt8juaQbdGxGm','johndoe@gmail.com','customer','john','071234545','2025-05-20 21:09:18','00518','00518','nairobi','Kenya','00100'),
(3,'peter','$2y$10$Uc.8QInKfzS5s975nx0GBuGfCdIlUlal/mbe7sCrkDY8a9Skp/Q8G','Peter@gmail.com','staff','peter','07234567','2025-05-20 21:14:13',NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'shakura'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-05-22  6:31:15
