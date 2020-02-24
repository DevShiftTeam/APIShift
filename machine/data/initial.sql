-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: apishift
-- ------------------------------------------------------
-- Server version	8.0.19

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_pages`
--

DROP TABLE IF EXISTS `admin_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `path` varchar(45) DEFAULT NULL,
  `icon` varchar(45) DEFAULT NULL,
  `parent` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_pages`
--

LOCK TABLES `admin_pages` WRITE;
/*!40000 ALTER TABLE `admin_pages` DISABLE KEYS */;
INSERT INTO `admin_pages` VALUES (1,'Database','database','fa fa-database',0),(2,'Session','session','fas fa-user',0),(3,'Logic','logic','fas fa-tasks',0),(4,'Access','access','fas fa-lock',0),(5,'Analysis','analysis','fas fa-chart-bar',0),(6,'Extensions','extensions','fas fa-microchip',0),(7,'Issues','issues','fa fa-ticket-alt',0),(8,'Settings','settings','fas fa-cog',0);
/*!40000 ALTER TABLE `admin_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(45) DEFAULT NULL,
  `password` char(60) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` VALUES (1,'admin','$2y$10$5/IfX63ViPwt2.BhYlds3udheXazXnoD.EUdN/ADsKNTweNt0qWRm','2020-02-19 14:54:40'),(3,'admin','$2y$10$pGQmMRnWGFt7wZreaSTTzu8MO1aPfEA8rvQTjCiM7d1yGkMfrKjzu','2020-02-20 22:26:06');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `connection_node_types`
--

DROP TABLE IF EXISTS `connection_node_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `connection_node_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `connection_node_types`
--

LOCK TABLES `connection_node_types` WRITE;
/*!40000 ALTER TABLE `connection_node_types` DISABLE KEYS */;
INSERT INTO `connection_node_types` VALUES (1,'Process'),(2,'Task'),(3,'Connection'),(4,'DataSource'),(5,'DataEntry');
/*!40000 ALTER TABLE `connection_node_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `connection_types`
--

DROP TABLE IF EXISTS `connection_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `connection_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `connection_types`
--

LOCK TABLES `connection_types` WRITE;
/*!40000 ALTER TABLE `connection_types` DISABLE KEYS */;
INSERT INTO `connection_types` VALUES (1,'Process'),(2,'Task'),(3,'Rule'),(4,'Function');
/*!40000 ALTER TABLE `connection_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `connections`
--

DROP TABLE IF EXISTS `connections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `connections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `connection_type` int DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `from_type` int DEFAULT NULL,
  `from` int DEFAULT NULL,
  `to_type` int DEFAULT NULL,
  `to` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `connections`
--

LOCK TABLES `connections` WRITE;
/*!40000 ALTER TABLE `connections` DISABLE KEYS */;
INSERT INTO `connections` VALUES (1,0,'username',5,2,5,4),(2,4,'password_verify',5,3,3,3),(3,0,'result',NULL,NULL,NULL,NULL),(4,0,'password',3,1,3,2),(5,0,'name',5,6,5,7),(6,3,'==',3,5,5,1);
/*!40000 ALTER TABLE `connections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_entries`
--

DROP TABLE IF EXISTS `data_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_entries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `type` int DEFAULT NULL,
  `source` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_entries`
--

LOCK TABLES `data_entries` WRITE;
/*!40000 ALTER TABLE `data_entries` DISABLE KEYS */;
INSERT INTO `data_entries` VALUES (1,'state',1,1),(2,'login',1,4),(3,'password',1,4),(4,'password',4,5),(5,'username',4,5),(6,'ADMIN_STATE',3,0),(7,'id',4,6);
/*!40000 ALTER TABLE `data_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_entry_types`
--

DROP TABLE IF EXISTS `data_entry_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_entry_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_entry_types`
--

LOCK TABLES `data_entry_types` WRITE;
/*!40000 ALTER TABLE `data_entry_types` DISABLE KEYS */;
INSERT INTO `data_entry_types` VALUES (1,'array_key'),(2,'variable'),(3,'constant'),(4,'table_cell');
/*!40000 ALTER TABLE `data_entry_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_source_types`
--

DROP TABLE IF EXISTS `data_source_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_source_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_source_types`
--

LOCK TABLES `data_source_types` WRITE;
/*!40000 ALTER TABLE `data_source_types` DISABLE KEYS */;
INSERT INTO `data_source_types` VALUES (1,'array'),(2,'table'),(3,'item'),(4,'relation'),(5,'static_class'),(6,'class_instance');
/*!40000 ALTER TABLE `data_source_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `data_sources`
--

DROP TABLE IF EXISTS `data_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_sources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `type` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `data_sources`
--

LOCK TABLES `data_sources` WRITE;
/*!40000 ALTER TABLE `data_sources` DISABLE KEYS */;
INSERT INTO `data_sources` VALUES (1,'_SESSION',1),(2,'_POST',1),(3,'_GET',1),(4,'params',1),(5,'admin_users',2),(6,'session_states',2);
/*!40000 ALTER TABLE `data_sources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `items` (
  `id` int NOT NULL,
  `table_name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `laguages`
--

DROP TABLE IF EXISTS `laguages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `laguages` (
  `id` int NOT NULL,
  `name` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `laguages`
--

LOCK TABLES `laguages` WRITE;
/*!40000 ALTER TABLE `laguages` DISABLE KEYS */;
/*!40000 ALTER TABLE `laguages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `process_connections`
--

DROP TABLE IF EXISTS `process_connections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `process_connections` (
  `process` int NOT NULL,
  `connection` int NOT NULL,
  PRIMARY KEY (`process`,`connection`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `process_connections`
--

LOCK TABLES `process_connections` WRITE;
/*!40000 ALTER TABLE `process_connections` DISABLE KEYS */;
INSERT INTO `process_connections` VALUES (1,1),(1,2),(1,3),(1,4),(2,5),(2,6);
/*!40000 ALTER TABLE `process_connections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `processes`
--

DROP TABLE IF EXISTS `processes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `processes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `processes`
--

LOCK TABLES `processes` WRITE;
/*!40000 ALTER TABLE `processes` DISABLE KEYS */;
INSERT INTO `processes` VALUES (1,'admin_auth'),(2,'admin_state_auth');
/*!40000 ALTER TABLE `processes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relation_types`
--

DROP TABLE IF EXISTS `relation_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `relation_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relation_types`
--

LOCK TABLES `relation_types` WRITE;
/*!40000 ALTER TABLE `relation_types` DISABLE KEYS */;
INSERT INTO `relation_types` VALUES (1,'One To One'),(2,'One To Many'),(3,'Many To Many');
/*!40000 ALTER TABLE `relation_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relations`
--

DROP TABLE IF EXISTS `relations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `relations` (
  `parent` int NOT NULL,
  `from` int NOT NULL,
  `to` int NOT NULL,
  `type` int NOT NULL,
  PRIMARY KEY (`parent`,`from`,`to`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relations`
--

LOCK TABLES `relations` WRITE;
/*!40000 ALTER TABLE `relations` DISABLE KEYS */;
/*!40000 ALTER TABLE `relations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `request_authorization`
--

DROP TABLE IF EXISTS `request_authorization`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `request_authorization` (
  `id` int NOT NULL AUTO_INCREMENT,
  `controller` text,
  `method` text,
  `task` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `request_authorization`
--

LOCK TABLES `request_authorization` WRITE;
/*!40000 ALTER TABLE `request_authorization` DISABLE KEYS */;
INSERT INTO `request_authorization` VALUES (1,'Control','*',2);
/*!40000 ALTER TABLE `request_authorization` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `session_state_structures`
--

DROP TABLE IF EXISTS `session_state_structures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `session_state_structures` (
  `id` int NOT NULL AUTO_INCREMENT,
  `state` int DEFAULT NULL,
  `key` varchar(45) DEFAULT NULL,
  `parent` int DEFAULT NULL,
  `entry` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session_state_structures`
--

LOCK TABLES `session_state_structures` WRITE;
/*!40000 ALTER TABLE `session_state_structures` DISABLE KEYS */;
/*!40000 ALTER TABLE `session_state_structures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `session_states`
--

DROP TABLE IF EXISTS `session_states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `session_states` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `inactive_timeout` int DEFAULT '0',
  `active_timeout` int DEFAULT '0',
  `auth_task` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session_states`
--

LOCK TABLES `session_states` WRITE;
/*!40000 ALTER TABLE `session_states` DISABLE KEYS */;
INSERT INTO `session_states` VALUES (1,'ADMIN_STATE',600,0,1);
/*!40000 ALTER TABLE `session_states` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `value` varchar(45) DEFAULT NULL,
  `parent` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `statuses`
--

DROP TABLE IF EXISTS `statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `statuses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `statuses`
--

LOCK TABLES `statuses` WRITE;
/*!40000 ALTER TABLE `statuses` DISABLE KEYS */;
/*!40000 ALTER TABLE `statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_processes`
--

DROP TABLE IF EXISTS `task_processes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_processes` (
  `task` int NOT NULL,
  `process` int NOT NULL,
  PRIMARY KEY (`task`,`process`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_processes`
--

LOCK TABLES `task_processes` WRITE;
/*!40000 ALTER TABLE `task_processes` DISABLE KEYS */;
INSERT INTO `task_processes` VALUES (1,1),(2,2);
/*!40000 ALTER TABLE `task_processes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES (1,'admin_auth'),(2,'admin_state_auth');
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `translatable_columns`
--

DROP TABLE IF EXISTS `translatable_columns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `translatable_columns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `table` int DEFAULT NULL,
  `name` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `translatable_columns`
--

LOCK TABLES `translatable_columns` WRITE;
/*!40000 ALTER TABLE `translatable_columns` DISABLE KEYS */;
/*!40000 ALTER TABLE `translatable_columns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `translations`
--

DROP TABLE IF EXISTS `translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `translations` (
  `column` int NOT NULL,
  `lang` int NOT NULL,
  `translation` text,
  PRIMARY KEY (`column`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `translations`
--

LOCK TABLES `translations` WRITE;
/*!40000 ALTER TABLE `translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `traslatable_tables`
--

DROP TABLE IF EXISTS `traslatable_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `traslatable_tables` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `traslatable_tables`
--

LOCK TABLES `traslatable_tables` WRITE;
/*!40000 ALTER TABLE `traslatable_tables` DISABLE KEYS */;
/*!40000 ALTER TABLE `traslatable_tables` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-02-21 20:46:50
