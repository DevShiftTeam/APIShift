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
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `path` varchar(45) DEFAULT NULL,
  `icon` varchar(45) DEFAULT NULL,
  `parent` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `admin_pages` WRITE;
INSERT INTO `admin_pages` VALUES (1, 'Database', 'database', 'mdi-database', 0),(2, 'Session', 'session', 'mdi-account', 0),(3, 'Logic', 'logic', 'mdi-graph', 0),(4, 'Access', 'access', 'mdi-lock', 0),(5, 'Analysis', 'analysis', 'mdi-chart-bar', 0),(6, 'Extensions', 'extensions', 'mdi-memory', 0),(7, 'Issues', 'issues', 'fa fa-ticket-alt', 0),(8, 'Settings', 'settings', 'mdi-cog-outline', 0),(9, 'Session Access', 'session', NULL, 4),(10, 'Controller Access', 'controllers', NULL, 4),(11, 'Database Access', 'database', NULL, 4),(12, 'Data Model Editor', 'model_editor', NULL, 1),(13, 'Database Connection List', 'database_list', NULL, 1),(14, 'Manage Pages', 'manage_pages', NULL, 8),(15, 'Admin Users Manager', 'admin_users', '', 8),(16, 'General Settings', 'general', '', 8);
UNLOCK TABLES;
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL,
  `password` char(60) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`,`username`) USING BTREE,
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
DROP TABLE IF EXISTS `connection_node_types`;
CREATE TABLE `connection_node_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `connection_node_types` WRITE;
INSERT INTO `connection_node_types` VALUES (1, 'Process'),(2, 'Task'),(3, 'Connection'),(4, 'DataSource'),(5, 'DataEntry');
UNLOCK TABLES;
DROP TABLE IF EXISTS `connection_types`;
CREATE TABLE `connection_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `connection_types` WRITE;
INSERT INTO `connection_types` VALUES (1, 'Process'),(2, 'Task'),(3, 'Rule'),(4, 'Function');
UNLOCK TABLES;
DROP TABLE IF EXISTS `connections`;
CREATE TABLE `connections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `connection_type` int(11) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `from_type` int(11) DEFAULT NULL,
  `from` int(11) DEFAULT NULL,
  `to_type` int(11) DEFAULT NULL,
  `to` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `connections` WRITE;
INSERT INTO `connections` VALUES (1, 0, 'username', 5, 2, 5, 4),(2, 4, 'password_verify', 5, 3, 3, 3),(3, 0, 'result', NULL, NULL, NULL, NULL),(4, 0, 'password', 3, 1, 3, 2),(5, 3, '==', 5, 8, 5, 1);
UNLOCK TABLES;
DROP TABLE IF EXISTS `data_entries`;
CREATE TABLE `data_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `source` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `data_entries` WRITE;
INSERT INTO `data_entries` VALUES (1, 'state', 1, 1),(2, 'login', 1, 2),(3, 'password', 1, 2),(4, 'password', 4, 5),(5, 'username', 4, 5),(6, 'ADMIN_STATE', 3, 0),(7, 'id', 4, 6),(8, 'state_id', 1, 4),(9, '1', 3, 0);
UNLOCK TABLES;
DROP TABLE IF EXISTS `data_entry_types`;
CREATE TABLE `data_entry_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `data_entry_types` WRITE;
INSERT INTO `data_entry_types` VALUES (1, 'array_key'),(2, 'variable'),(3, 'constant'),(4, 'table_cell');
UNLOCK TABLES;
DROP TABLE IF EXISTS `data_source_types`;
CREATE TABLE `data_source_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `data_source_types` WRITE;
INSERT INTO `data_source_types` VALUES (1, 'array'),(2, 'table'),(3, 'item'),(4, 'relation'),(5, 'static_class'),(6, 'class_instance');
UNLOCK TABLES;
DROP TABLE IF EXISTS `data_sources`;
CREATE TABLE `data_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `data_sources` WRITE;
INSERT INTO `data_sources` VALUES (1, '_SESSION', 1),(2, '_POST', 1),(3, '_GET', 1),(4, 'task_inputs', 1),(5, 'admin_users', 2),(6, 'session_states', 2);
UNLOCK TABLES;
DROP TABLE IF EXISTS `databases`;
CREATE TABLE `databases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `host` varchar(45) DEFAULT NULL,
  `user` varchar(45) DEFAULT NULL,
  `pass` varchar(45) DEFAULT NULL,
  `db` varchar(45) DEFAULT NULL,
  `port` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
DROP TABLE IF EXISTS `input_values`;
CREATE TABLE `input_values` (
  `id` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `is_source` tinyint(1) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`,`value`,`is_source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `input_values` WRITE;
INSERT INTO `input_values` VALUES (1, 9, 0, 'state_id');
UNLOCK TABLES;
DROP TABLE IF EXISTS `inputs`;
CREATE TABLE `inputs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `inputs` WRITE;
INSERT INTO `inputs` VALUES (1, 'state_ADMIN_STATE');
UNLOCK TABLES;
DROP TABLE IF EXISTS `items`;
CREATE TABLE `items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `items` WRITE;

UNLOCK TABLES;
DROP TABLE IF EXISTS `laguages`;
CREATE TABLE `laguages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
DROP TABLE IF EXISTS `process_connections`;
CREATE TABLE `process_connections` (
  `process` int(11) NOT NULL,
  `connection` int(11) NOT NULL,
  PRIMARY KEY (`process`,`connection`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `process_connections` WRITE;
INSERT INTO `process_connections` VALUES (1, 1),(1, 2),(1, 3),(1, 4),(2, 5);
UNLOCK TABLES;
DROP TABLE IF EXISTS `processes`;
CREATE TABLE `processes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `processes` WRITE;
INSERT INTO `processes` VALUES (1, 'admin_auth'),(2, 'state_auth');
UNLOCK TABLES;
DROP TABLE IF EXISTS `relation_types`;
CREATE TABLE `relation_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `relation_types` WRITE;
INSERT INTO `relation_types` VALUES (1, 'One To One'),(2, 'One To Many'),(3, 'Many To Many');
UNLOCK TABLES;
DROP TABLE IF EXISTS `relations`;
CREATE TABLE `relations` (
  `parent` int(11) NOT NULL,
  `from` int(11) NOT NULL,
  `to` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  PRIMARY KEY (`parent`,`from`,`to`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `request_authorization`;
CREATE TABLE `request_authorization` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `controller` text DEFAULT NULL,
  `method` text DEFAULT NULL,
  `task` int(11) DEFAULT NULL,
  `input` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `request_authorization` WRITE;
INSERT INTO `request_authorization` VALUES (1, 'Admin*', '*', 2, 1);
UNLOCK TABLES;
DROP TABLE IF EXISTS `session_state_structures`;
CREATE TABLE `session_state_structures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state` int(11) DEFAULT NULL,
  `key` varchar(45) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  `task` int(11) DEFAULT NULL,
  `input` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
DROP TABLE IF EXISTS `session_states`;
CREATE TABLE `session_states` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `inactive_timeout` int(11) DEFAULT 0,
  `active_timeout` int(11) DEFAULT 0,
  `auth_task` int(11) DEFAULT NULL,
  `parent` int(11) DEFAULT 0,
  `auth_input` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `session_states` WRITE;
INSERT INTO `session_states` VALUES (1, 'ADMIN_STATE', 600, 0, 1, 0, NULL);
UNLOCK TABLES;
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `value` varchar(45) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
DROP TABLE IF EXISTS `statuses`;
CREATE TABLE `statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
DROP TABLE IF EXISTS `task_processes`;
CREATE TABLE `task_processes` (
  `task` int(11) NOT NULL,
  `process` int(11) NOT NULL,
  PRIMARY KEY (`task`,`process`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `task_processes` WRITE;
INSERT INTO `task_processes` VALUES (1, 1),(2, 2),(3, 3);
UNLOCK TABLES;
DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `tasks` WRITE;
INSERT INTO `tasks` VALUES (1, 'admin_auth'),(2, 'state_auth');
UNLOCK TABLES;
DROP TABLE IF EXISTS `translatable_columns`;
CREATE TABLE `translatable_columns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table` int(11) DEFAULT NULL,
  `name` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
DROP TABLE IF EXISTS `translations`;
CREATE TABLE `translations` (
  `column` int(11) NOT NULL,
  `lang` int(11) NOT NULL,
  `translation` text DEFAULT NULL,
  PRIMARY KEY (`column`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
DROP TABLE IF EXISTS `traslatable_tables`;
CREATE TABLE `traslatable_tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
