-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: mysql_web:3306
-- Generation Time: Mar 22, 2021 at 01:22 PM
-- Server version: 8.0.23
-- PHP Version: 7.4.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Table structure for table `admin_pages`
--

CREATE TABLE `admin_pages` (
  `id` int NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `path` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `icon` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `parent` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_pages`
--

INSERT INTO `admin_pages` (`id`, `name`, `path`, `icon`, `parent`) VALUES
(1, 'Database', 'database', 'mdi-database', 0),
(2, 'Session', 'session', 'mdi-account', 0),
(3, 'Logic', 'logic', 'mdi-graph', 0),
(4, 'Access', 'access', 'mdi-lock', 0),
(5, 'Analysis', 'analysis', 'mdi-chart-bar', 0),
(6, 'Extensions', 'extensions', 'mdi-memory', 0),
(7, 'Issues', 'issues', 'fa fa-ticket-alt', 0),
(8, 'Settings', 'settings', 'mdi-cog-outline', 0),
(9, 'Session Access', 'session', NULL, 4),
(10, 'Controller Access', 'controllers', NULL, 4),
(11, 'Database Access', 'database', NULL, 4),
(12, 'Data Model Editor', 'model_editor', NULL, 1),
(13, 'Database Connection List', 'database_list', NULL, 1),
(14, 'Manage Pages', 'manage_pages', NULL, 8),
(15, 'Admin Users Manager', 'admin_users', '', 8),
(16, 'General Settings', 'general', '', 8);

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int NOT NULL,
  `username` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `password` char(60) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `created`) VALUES
(1, 'admin', '$2y$10$u4K7M74ZP5gotxKagTB6l.W/EL6kvYYW43KkSkQUrwuFOIt.vdsMO', '2021-03-14 10:35:12');

-- --------------------------------------------------------

--
-- Table structure for table `connections`
--

CREATE TABLE `connections` (
  `id` int NOT NULL,
  `connection_type` int DEFAULT NULL,
  `name` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `from_type` int DEFAULT NULL,
  `from` int DEFAULT NULL,
  `to_type` int DEFAULT NULL,
  `to` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `connections`
--

INSERT INTO `connections` (`id`, `connection_type`, `name`, `from_type`, `from`, `to_type`, `to`) VALUES
(1, 0, 'username', 5, 2, 5, 4),
(2, 4, 'password_verify', 5, 3, 3, 3),
(3, 0, 'result', NULL, NULL, NULL, NULL),
(4, 0, 'password', 3, 1, 3, 2),
(5, 3, '==', 5, 8, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `connection_node_types`
--

CREATE TABLE `connection_node_types` (
  `id` int NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `connection_node_types`
--

INSERT INTO `connection_node_types` (`id`, `name`) VALUES
(1, 'Process'),
(2, 'Task'),
(3, 'Connection'),
(4, 'DataSource'),
(5, 'DataEntry');

-- --------------------------------------------------------

--
-- Table structure for table `connection_types`
--

CREATE TABLE `connection_types` (
  `id` int NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `connection_types`
--

INSERT INTO `connection_types` (`id`, `name`) VALUES
(1, 'Process'),
(2, 'Task'),
(3, 'Rule'),
(4, 'Function');

-- --------------------------------------------------------

--
-- Table structure for table `databases`
--

CREATE TABLE `databases` (
  `id` int NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `host` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pass` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `db` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `port` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `data_entries`
--

CREATE TABLE `data_entries` (
  `id` int NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` int DEFAULT NULL,
  `source` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_entries`
--

INSERT INTO `data_entries` (`id`, `name`, `type`, `source`) VALUES
(1, 'state', 1, 1),
(2, 'login', 1, 2),
(3, 'password', 1, 2),
(4, 'password', 4, 5),
(5, 'username', 4, 5),
(6, 'ADMIN_STATE', 3, 0),
(7, 'id', 4, 6),
(8, 'state_id', 1, 4),
(9, '1', 3, 0);

-- --------------------------------------------------------

--
-- Table structure for table `data_entry_types`
--

CREATE TABLE `data_entry_types` (
  `id` int NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_entry_types`
--

INSERT INTO `data_entry_types` (`id`, `name`) VALUES
(1, 'array_key'),
(2, 'variable'),
(3, 'constant'),
(4, 'table_cell');

-- --------------------------------------------------------

--
-- Table structure for table `data_sources`
--

CREATE TABLE `data_sources` (
  `id` int NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_sources`
--

INSERT INTO `data_sources` (`id`, `name`, `type`) VALUES
(1, '_SESSION', 1),
(2, '_POST', 1),
(3, '_GET', 1),
(4, 'task_inputs', 1),
(5, 'admin_users', 2),
(6, 'session_states', 2);

-- --------------------------------------------------------

--
-- Table structure for table `data_source_types`
--

CREATE TABLE `data_source_types` (
  `id` int NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_source_types`
--

INSERT INTO `data_source_types` (`id`, `name`) VALUES
(1, 'array'),
(2, 'table'),
(3, 'item'),
(4, 'relation'),
(5, 'static_class'),
(6, 'class_instance');

-- --------------------------------------------------------

--
-- Table structure for table `enums`
--

CREATE TABLE `enums` (
  `id` int NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enum_types`
--

CREATE TABLE `enum_types` (
  `id` int NOT NULL,
  `name` text NOT NULL,
  `enum_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inputs`
--

CREATE TABLE `inputs` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inputs`
--

INSERT INTO `inputs` (`id`, `name`) VALUES
(1, 'state_ADMIN_STATE');

-- --------------------------------------------------------

--
-- Table structure for table `input_values`
--

CREATE TABLE `input_values` (
  `id` int NOT NULL,
  `value` int NOT NULL,
  `is_source` tinyint(1) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `input_values`
--

INSERT INTO `input_values` (`id`, `value`, `is_source`, `name`) VALUES
(1, 9, 0, 'state_id');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int NOT NULL,
  `table_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `database_name` text COLLATE utf8mb4_general_ci NOT NULL,
  `parent` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laguages`
--

CREATE TABLE `laguages` (
  `id` int NOT NULL,
  `name` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orm_graphs`
--

CREATE TABLE `orm_graphs` (
  `id` int NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orm_graph_elements`
--

CREATE TABLE `orm_graph_elements` (
  `id` int NOT NULL,
  `element_id` int NOT NULL,
  `type` int NOT NULL,
  `position_x` float NOT NULL,
  `position_y` float NOT NULL,
  `z_index` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orm_graph_elemet_types`
--

CREATE TABLE `orm_graph_elemet_types` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `component_index` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orm_graph_elemet_types`
--

INSERT INTO `orm_graph_elemet_types` (`id`, `name`, `component_index`) VALUES
(1, 'item', 0),
(2, 'relation', 1),
(3, 'enum_type', 2),
(4, 'enum', 3),
(5, 'group', 4);

-- --------------------------------------------------------

--
-- Table structure for table `processes`
--

CREATE TABLE `processes` (
  `id` int NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `processes`
--

INSERT INTO `processes` (`id`, `name`) VALUES
(1, 'admin_auth'),
(2, 'state_auth');

-- --------------------------------------------------------

--
-- Table structure for table `process_connections`
--

CREATE TABLE `process_connections` (
  `process` int NOT NULL,
  `connection` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `process_connections`
--

INSERT INTO `process_connections` (`process`, `connection`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(2, 5);

-- --------------------------------------------------------

--
-- Table structure for table `relations`
--

CREATE TABLE `relations` (
  `parent` int NOT NULL,
  `from` int NOT NULL,
  `to` int NOT NULL,
  `type` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `relation_types`
--

CREATE TABLE `relation_types` (
  `id` int NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `relation_types`
--

INSERT INTO `relation_types` (`id`, `name`) VALUES
(1, 'One To One'),
(2, 'One To Many'),
(3, 'Many To Many');

-- --------------------------------------------------------

--
-- Table structure for table `request_authorization`
--

CREATE TABLE `request_authorization` (
  `id` int NOT NULL,
  `controller` text COLLATE utf8mb4_general_ci,
  `method` text COLLATE utf8mb4_general_ci,
  `task` int DEFAULT NULL,
  `input` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request_authorization`
--

INSERT INTO `request_authorization` (`id`, `controller`, `method`, `task`, `input`) VALUES
(1, 'Admin*', '*', 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `session_states`
--

CREATE TABLE `session_states` (
  `id` int NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `inactive_timeout` int DEFAULT '0',
  `active_timeout` int DEFAULT '0',
  `auth_task` int DEFAULT NULL,
  `parent` int DEFAULT '0',
  `auth_input` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `session_states`
--

INSERT INTO `session_states` (`id`, `name`, `inactive_timeout`, `active_timeout`, `auth_task`, `parent`, `auth_input`) VALUES
(1, 'ADMIN_STATE', 600, 0, 1, 0, NULL),
(2, 'User', 0, 0, NULL, 0, NULL),
(3, 'Premium', 0, 0, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `session_state_structures`
--

CREATE TABLE `session_state_structures` (
  `id` int NOT NULL,
  `state` int DEFAULT NULL,
  `key` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `parent` int DEFAULT NULL,
  `task` int DEFAULT NULL,
  `input` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `value` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `parent` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

CREATE TABLE `statuses` (
  `id` int NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `name`) VALUES
(1, 'admin_auth'),
(2, 'state_auth');

-- --------------------------------------------------------

--
-- Table structure for table `task_processes`
--

CREATE TABLE `task_processes` (
  `task` int NOT NULL,
  `process` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_processes`
--

INSERT INTO `task_processes` (`task`, `process`) VALUES
(1, 1),
(2, 2),
(3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `translatable_columns`
--

CREATE TABLE `translatable_columns` (
  `id` int NOT NULL,
  `table` int DEFAULT NULL,
  `name` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `translations`
--

CREATE TABLE `translations` (
  `column` int NOT NULL,
  `lang` int NOT NULL,
  `translation` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `traslatable_tables`
--

CREATE TABLE `traslatable_tables` (
  `id` int NOT NULL,
  `name` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_pages`
--
ALTER TABLE `admin_pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`,`username`) USING BTREE,
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `connections`
--
ALTER TABLE `connections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `connection_node_types`
--
ALTER TABLE `connection_node_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `connection_types`
--
ALTER TABLE `connection_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `databases`
--
ALTER TABLE `databases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `data_entries`
--
ALTER TABLE `data_entries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `data_entry_types`
--
ALTER TABLE `data_entry_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `data_sources`
--
ALTER TABLE `data_sources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `data_source_types`
--
ALTER TABLE `data_source_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enums`
--
ALTER TABLE `enums`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enum_types`
--
ALTER TABLE `enum_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inputs`
--
ALTER TABLE `inputs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `input_values`
--
ALTER TABLE `input_values`
  ADD PRIMARY KEY (`id`,`value`,`is_source`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `laguages`
--
ALTER TABLE `laguages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orm_graphs`
--
ALTER TABLE `orm_graphs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orm_graph_elements`
--
ALTER TABLE `orm_graph_elements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orm_graph_elemet_types`
--
ALTER TABLE `orm_graph_elemet_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `processes`
--
ALTER TABLE `processes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `process_connections`
--
ALTER TABLE `process_connections`
  ADD PRIMARY KEY (`process`,`connection`);

--
-- Indexes for table `relations`
--
ALTER TABLE `relations`
  ADD PRIMARY KEY (`parent`,`from`,`to`,`type`);

--
-- Indexes for table `relation_types`
--
ALTER TABLE `relation_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `request_authorization`
--
ALTER TABLE `request_authorization`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `session_states`
--
ALTER TABLE `session_states`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `session_state_structures`
--
ALTER TABLE `session_state_structures`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `statuses`
--
ALTER TABLE `statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task_processes`
--
ALTER TABLE `task_processes`
  ADD PRIMARY KEY (`task`,`process`);

--
-- Indexes for table `translatable_columns`
--
ALTER TABLE `translatable_columns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `translations`
--
ALTER TABLE `translations`
  ADD PRIMARY KEY (`column`,`lang`);

--
-- Indexes for table `traslatable_tables`
--
ALTER TABLE `traslatable_tables`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_pages`
--
ALTER TABLE `admin_pages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `connections`
--
ALTER TABLE `connections`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `connection_node_types`
--
ALTER TABLE `connection_node_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `connection_types`
--
ALTER TABLE `connection_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `databases`
--
ALTER TABLE `databases`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `data_entries`
--
ALTER TABLE `data_entries`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `data_entry_types`
--
ALTER TABLE `data_entry_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `data_sources`
--
ALTER TABLE `data_sources`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `data_source_types`
--
ALTER TABLE `data_source_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `enums`
--
ALTER TABLE `enums`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enum_types`
--
ALTER TABLE `enum_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inputs`
--
ALTER TABLE `inputs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laguages`
--
ALTER TABLE `laguages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orm_graphs`
--
ALTER TABLE `orm_graphs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orm_graph_elements`
--
ALTER TABLE `orm_graph_elements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orm_graph_elemet_types`
--
ALTER TABLE `orm_graph_elemet_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `processes`
--
ALTER TABLE `processes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `relation_types`
--
ALTER TABLE `relation_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `request_authorization`
--
ALTER TABLE `request_authorization`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `session_states`
--
ALTER TABLE `session_states`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `session_state_structures`
--
ALTER TABLE `session_state_structures`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `statuses`
--
ALTER TABLE `statuses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `translatable_columns`
--
ALTER TABLE `translatable_columns`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `traslatable_tables`
--
ALTER TABLE `traslatable_tables`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
