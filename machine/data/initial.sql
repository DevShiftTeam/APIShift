DROP TABLE IF EXISTS `admin_pages`;
CREATE TABLE `admin_pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `path` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `icon` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `parent` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `admin_pages` WRITE;
INSERT INTO `admin_pages` VALUES (1, 'Database', 'database', 'mdi-database', 0),(2, 'Session', 'session', 'mdi-account', 0),(3, 'Logic', 'logic', 'mdi-graph', 0),(4, 'Access', 'access', 'mdi-lock', 0),(5, 'Analysis', 'analysis', 'mdi-chart-bar', 0),(6, 'Extensions', 'extensions', 'mdi-memory', 0),(7, 'Issues', 'issues', 'fa fa-ticket-alt', 0),(8, 'Settings', 'settings', 'mdi-cog-outline', 0),(9, 'Session Access', 'session', NULL, 4),(10, 'Controller Access', 'controllers', NULL, 4),(11, 'Database Access', 'database', NULL, 4),(12, 'Data Model Editor', 'model_editor', NULL, 1),(13, 'Database Connection List', 'database_list', NULL, 1),(14, 'Manage Pages', 'manage_pages', NULL, 8),(15, 'Admin Users Manager', 'admin_users', '', 8),(16, 'General Settings', 'general', '', 8);
UNLOCK TABLES;
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` char(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`,`username`) USING BTREE,
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `connection_node_types`;
CREATE TABLE `connection_node_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `connection_node_types` WRITE;
INSERT INTO `connection_node_types` VALUES (1, 'Process'),(2, 'Task'),(3, 'Connection'),(4, 'DataSource'),(5, 'DataEntry');
UNLOCK TABLES;
DROP TABLE IF EXISTS `connection_types`;
CREATE TABLE `connection_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `connection_types` WRITE;
INSERT INTO `connection_types` VALUES (1, 'Process'),(2, 'Task'),(3, 'Rule'),(4, 'Function');
UNLOCK TABLES;
DROP TABLE IF EXISTS `connections`;
CREATE TABLE `connections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `connection_type` int DEFAULT NULL,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `from_type` int DEFAULT NULL,
  `from` int DEFAULT NULL,
  `to_type` int DEFAULT NULL,
  `to` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `connections` WRITE;
INSERT INTO `connections` VALUES (1, 0, 'username', 5, 2, 5, 4),(2, 4, 'password_verify', 5, 3, 3, 3),(3, 0, 'result', NULL, NULL, NULL, NULL),(4, 0, 'password', 3, 1, 3, 2),(5, 3, '==', 5, 8, 5, 1);
UNLOCK TABLES;
DROP TABLE IF EXISTS `data_entries`;
CREATE TABLE `data_entries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` int DEFAULT NULL,
  `source` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `data_entries` WRITE;
INSERT INTO `data_entries` VALUES (1, 'state', 1, 1),(2, 'login', 1, 2),(3, 'password', 1, 2),(4, 'password', 4, 5),(5, 'username', 4, 5),(6, 'ADMIN_STATE', 3, 0),(7, 'id', 4, 6),(8, 'state_id', 1, 4),(9, '1', 3, 0);
UNLOCK TABLES;
DROP TABLE IF EXISTS `data_entry_types`;
CREATE TABLE `data_entry_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `data_entry_types` WRITE;
INSERT INTO `data_entry_types` VALUES (1, 'array_key'),(2, 'variable'),(3, 'constant'),(4, 'table_cell');
UNLOCK TABLES;
DROP TABLE IF EXISTS `data_source_types`;
CREATE TABLE `data_source_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `data_source_types` WRITE;
INSERT INTO `data_source_types` VALUES (1, 'array'),(2, 'table'),(3, 'item'),(4, 'relation'),(5, 'static_class'),(6, 'class_instance');
UNLOCK TABLES;
DROP TABLE IF EXISTS `data_sources`;
CREATE TABLE `data_sources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `data_sources` WRITE;
INSERT INTO `data_sources` VALUES (1, '_SESSION', 1),(2, '_POST', 1),(3, '_GET', 1),(4, 'task_inputs', 1),(5, 'admin_users', 2),(6, 'session_states', 2);
UNLOCK TABLES;
DROP TABLE IF EXISTS `databases`;
CREATE TABLE `databases` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `host` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pass` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `db` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `port` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `databases` WRITE;

UNLOCK TABLES;
DROP TABLE IF EXISTS `enum_types`;
CREATE TABLE `enum_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `enum_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `enum_types` WRITE;

UNLOCK TABLES;
DROP TABLE IF EXISTS `enums`;
CREATE TABLE `enums` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `enums` WRITE;

UNLOCK TABLES;
DROP TABLE IF EXISTS `input_values`;
CREATE TABLE `input_values` (
  `id` int NOT NULL,
  `value` int NOT NULL,
  `is_source` tinyint(1) NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`,`value`,`is_source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `input_values` WRITE;
INSERT INTO `input_values` VALUES (1, 9, 0, 'state_id');
UNLOCK TABLES;
DROP TABLE IF EXISTS `inputs`;
CREATE TABLE `inputs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `inputs` WRITE;
INSERT INTO `inputs` VALUES (1, 'state_ADMIN_STATE');
UNLOCK TABLES;
DROP TABLE IF EXISTS `item_enums`;
CREATE TABLE `item_enums` (
  `item_id` int NOT NULL,
  `enum_id` int NOT NULL,
  PRIMARY KEY (`item_id`,`enum_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `item_enums` WRITE;

UNLOCK TABLES;
DROP TABLE IF EXISTS `items`;
CREATE TABLE `items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `table_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `database_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `parent` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `items` WRITE;

UNLOCK TABLES;
DROP TABLE IF EXISTS `laguages`;
CREATE TABLE `laguages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `laguages` WRITE;

UNLOCK TABLES;
DROP TABLE IF EXISTS `orm_graph_element_types`;
CREATE TABLE `orm_graph_element_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `component_index` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `orm_graph_element_types` WRITE;
INSERT INTO `orm_graph_element_types` VALUES (1, 'item', 0),(2, 'relation', 1),(3, 'enum_type', 2),(4, 'enum', 3),(5, 'group', 4);
UNLOCK TABLES;
DROP TABLE IF EXISTS `orm_graph_elements`;
CREATE TABLE `orm_graph_elements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `element_id` int NOT NULL,
  `type` int NOT NULL,
  `position_x` float NOT NULL,
  `position_y` float NOT NULL,
  `z_index` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `orm_graph_elements` WRITE;

UNLOCK TABLES;
DROP TABLE IF EXISTS `orm_graphs`;
CREATE TABLE `orm_graphs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `orm_graphs` WRITE;

UNLOCK TABLES;
DROP TABLE IF EXISTS `process_connections`;
CREATE TABLE `process_connections` (
  `process` int NOT NULL,
  `connection` int NOT NULL,
  PRIMARY KEY (`process`,`connection`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `process_connections` WRITE;
INSERT INTO `process_connections` VALUES (1, 1),(1, 2),(1, 3),(1, 4),(2, 5);
UNLOCK TABLES;
DROP TABLE IF EXISTS `processes`;
CREATE TABLE `processes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `processes` WRITE;
INSERT INTO `processes` VALUES (1, 'admin_auth'),(2, 'state_auth');
UNLOCK TABLES;
DROP TABLE IF EXISTS `relation_types`;
CREATE TABLE `relation_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `relation_types` WRITE;
INSERT INTO `relation_types` VALUES (1, 'One To One'),(2, 'One To Many'),(3, 'Many To Many');
UNLOCK TABLES;
DROP TABLE IF EXISTS `relations`;
CREATE TABLE `relations` (
  `parent` int NOT NULL,
  `from` int NOT NULL,
  `to` int NOT NULL,
  `type` int NOT NULL,
  PRIMARY KEY (`parent`,`from`,`to`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `relations` WRITE;

UNLOCK TABLES;
DROP TABLE IF EXISTS `request_authorization`;
CREATE TABLE `request_authorization` (
  `id` int NOT NULL AUTO_INCREMENT,
  `controller` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `method` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `task` int DEFAULT NULL,
  `input` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `request_authorization` WRITE;
INSERT INTO `request_authorization` VALUES (1, 'Admin\\*', '*', 2, 1);
UNLOCK TABLES;
DROP TABLE IF EXISTS `session_state_structures`;
CREATE TABLE `session_state_structures` (
  `id` int NOT NULL AUTO_INCREMENT,
  `state` int DEFAULT NULL,
  `key` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `parent` int DEFAULT NULL,
  `task` int DEFAULT NULL,
  `input` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `session_state_structures` WRITE;

UNLOCK TABLES;
DROP TABLE IF EXISTS `session_states`;
CREATE TABLE `session_states` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `inactive_timeout` int DEFAULT '0',
  `active_timeout` int DEFAULT '0',
  `auth_task` int DEFAULT NULL,
  `parent` int DEFAULT '0',
  `auth_input` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `session_states` WRITE;
INSERT INTO `session_states` VALUES (1, 'ADMIN_STATE', 600, 0, 1, 0, NULL);
UNLOCK TABLES;
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `value` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `parent` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `settings` WRITE;

UNLOCK TABLES;
DROP TABLE IF EXISTS `statuses`;
CREATE TABLE `statuses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `statuses` WRITE;

UNLOCK TABLES;
DROP TABLE IF EXISTS `task_processes`;
CREATE TABLE `task_processes` (
  `task` int NOT NULL,
  `process` int NOT NULL,
  PRIMARY KEY (`task`,`process`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `task_processes` WRITE;
INSERT INTO `task_processes` VALUES (1, 1),(2, 2),(3, 3);
UNLOCK TABLES;
DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `tasks` WRITE;
INSERT INTO `tasks` VALUES (1, 'admin_auth'),(2, 'state_auth');
UNLOCK TABLES;
DROP TABLE IF EXISTS `translatable_columns`;
CREATE TABLE `translatable_columns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `table` int DEFAULT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `translatable_columns` WRITE;

UNLOCK TABLES;
DROP TABLE IF EXISTS `translations`;
CREATE TABLE `translations` (
  `column` int NOT NULL,
  `lang` int NOT NULL,
  `translation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`column`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `translations` WRITE;

UNLOCK TABLES;
DROP TABLE IF EXISTS `traslatable_tables`;
CREATE TABLE `traslatable_tables` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
LOCK TABLES `traslatable_tables` WRITE;

UNLOCK TABLES;
