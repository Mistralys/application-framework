-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 30, 2018 at 12:15 PM
-- Server version: 8.0.11
-- PHP Version: 7.3.0alpha3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `maileditor_trunk_hosting`
--

-- --------------------------------------------------------

--
-- Table structure for table `app_locking`
--

CREATE TABLE `app_locking` (
  `lock_id` bigint(11) UNSIGNED NOT NULL,
  `screen_url_path` varchar(250) NOT NULL,
  `screen_name` varchar(250) NOT NULL DEFAULT '',
  `item_primary` varchar(250) NOT NULL DEFAULT '',
  `lock_label` varchar(250) NOT NULL DEFAULT '',
  `locked_by` int(11) UNSIGNED NOT NULL,
  `locked_time` datetime NOT NULL,
  `locked_until` datetime NOT NULL,
  `last_activity` datetime NOT NULL,
  `properties` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `app_locking_messages`
--

CREATE TABLE `app_locking_messages` (
  `lock_id` bigint(11) UNSIGNED NOT NULL,
  `requested_by` int(11) UNSIGNED NOT NULL,
  `message_id` bigint(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `app_messagelog`
--

CREATE TABLE `app_messagelog` (
  `log_id` bigint(11) UNSIGNED NOT NULL,
  `date` datetime NOT NULL,
  `type` varchar(60) NOT NULL,
  `message` text NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `category` varchar(180) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `app_messaging`
--

CREATE TABLE `app_messaging` (
  `message_id` bigint(11) UNSIGNED NOT NULL,
  `in_reply_to` bigint(11) UNSIGNED DEFAULT NULL,
  `from_user` int(11) UNSIGNED NOT NULL,
  `to_user` int(11) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `priority` varchar(60) NOT NULL DEFAULT 'normal',
  `date_sent` datetime NOT NULL,
  `date_received` datetime DEFAULT NULL,
  `date_responded` datetime DEFAULT NULL,
  `response` text,
  `custom_data` text,
  `lock_id` bigint(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
  `data_key` varchar(80) NOT NULL,
  `data_value` mediumtext NOT NULL,
  `data_role` enum('cache','persistent') NOT NULL DEFAULT 'cache'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `country_id` int(11) UNSIGNED NOT NULL,
  `iso` varchar(2) NOT NULL,
  `label` varchar(180) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cache for Editor countries';

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`country_id`, `iso`, `label`) VALUES
(1, 'de', 'Germany'),
(2, 'fr', 'France'),
(3, 'es', 'Spain'),
(4, 'uk', 'United Kingdom'),
(5, 'pl', 'Poland'),
(6, 'it', 'Italy'),
(7, 'us', 'United States'),
(8, 'ro', 'Romania'),
(9, 'ca', 'Canada'),
(10, 'at', 'Austria'),
(11, 'mx', 'Mexico'),
(9999, 'zz', 'Country-independent');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `feedback` text NOT NULL,
  `request_params` text NOT NULL,
  `feedback_scope` varchar(40) NOT NULL DEFAULT 'application',
  `feedback_type` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `known_users`
--

CREATE TABLE `known_users` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `foreign_id` varchar(250) NOT NULL,
  `firstname` varchar(250) NOT NULL,
  `lastname` varchar(250) NOT NULL,
  `email` varchar(254) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `known_users`
--

INSERT INTO `known_users` (`user_id`, `foreign_id`, `firstname`, `lastname`, `email`) VALUES
(1, '__system', 'SPIN', 'System', 's.mordziol@mistralys.eu'),
(2, '1234567890', 'Otto', 'Mustermann', 's.mordziol@mistralys.eu'),
(3, '21225378', 'Sebastian', 'Mordziol', 's.mordziol@mistralys.com');

-- --------------------------------------------------------

--
-- Table structure for table `locales_application`
--

CREATE TABLE `locales_application` (
  `locale_name` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `locales_application`
--

INSERT INTO `locales_application` (`locale_name`) VALUES
('de_DE'),
('en_UK');

-- --------------------------------------------------------

--
-- Table structure for table `locales_content`
--

CREATE TABLE `locales_content` (
  `locale_name` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `locales_content`
--

INSERT INTO `locales_content` (`locale_name`) VALUES
('de_DE'),
('en_UK');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `media_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `media_date_added` datetime NOT NULL,
  `media_type` varchar(100) NOT NULL,
  `media_name` varchar(240) NOT NULL,
  `media_extension` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `media_configurations`
--

CREATE TABLE `media_configurations` (
  `config_id` int(11) UNSIGNED NOT NULL,
  `type_id` varchar(60) NOT NULL,
  `config_key` varchar(32) NOT NULL,
  `config` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `uploads`
--

CREATE TABLE `uploads` (
  `upload_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `upload_date` datetime NOT NULL,
  `upload_name` varchar(240) NOT NULL,
  `upload_extension` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `setting_name` varchar(180) NOT NULL,
  `setting_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_locking`
--
ALTER TABLE `app_locking`
  ADD PRIMARY KEY (`lock_id`),
  ADD UNIQUE KEY `screen_url_path` (`screen_url_path`,`item_primary`) USING BTREE,
  ADD KEY `locked_by` (`locked_by`),
  ADD KEY `locked_time` (`locked_time`),
  ADD KEY `locked_until` (`locked_until`),
  ADD KEY `last_activity` (`last_activity`);

--
-- Indexes for table `app_locking_messages`
--
ALTER TABLE `app_locking_messages`
  ADD PRIMARY KEY (`lock_id`,`requested_by`),
  ADD KEY `message_id` (`message_id`),
  ADD KEY `lock_id` (`lock_id`),
  ADD KEY `requested_by` (`requested_by`);

--
-- Indexes for table `app_messagelog`
--
ALTER TABLE `app_messagelog`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `type` (`type`),
  ADD KEY `user_id_2` (`user_id`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `app_messaging`
--
ALTER TABLE `app_messaging`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `from_user` (`from_user`),
  ADD KEY `to_user` (`to_user`),
  ADD KEY `priority` (`priority`),
  ADD KEY `date_sent` (`date_sent`),
  ADD KEY `date_received` (`date_received`),
  ADD KEY `date_responded` (`date_responded`),
  ADD KEY `reply_to` (`in_reply_to`),
  ADD KEY `lock_id` (`lock_id`);

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`data_key`),
  ADD KEY `data_role` (`data_role`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`country_id`),
  ADD KEY `iso` (`iso`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `user_id` (`user_id`,`date`,`feedback_scope`),
  ADD KEY `feedback_type` (`feedback_type`);

--
-- Indexes for table `known_users`
--
ALTER TABLE `known_users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `foreign_id` (`foreign_id`);

--
-- Indexes for table `locales_application`
--
ALTER TABLE `locales_application`
  ADD PRIMARY KEY (`locale_name`);

--
-- Indexes for table `locales_content`
--
ALTER TABLE `locales_content`
  ADD PRIMARY KEY (`locale_name`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`media_id`),
  ADD KEY `user_id` (`user_id`,`media_type`);

--
-- Indexes for table `media_configurations`
--
ALTER TABLE `media_configurations`
  ADD PRIMARY KEY (`config_id`),
  ADD KEY `type_id` (`type_id`,`config_key`);

--
-- Indexes for table `uploads`
--
ALTER TABLE `uploads`
  ADD PRIMARY KEY (`upload_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`user_id`,`setting_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `app_locking`
--
ALTER TABLE `app_locking`
  MODIFY `lock_id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109170;

--
-- AUTO_INCREMENT for table `app_messagelog`
--
ALTER TABLE `app_messagelog`
  MODIFY `log_id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=562;

--
-- AUTO_INCREMENT for table `app_messaging`
--
ALTER TABLE `app_messaging`
  MODIFY `message_id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `country_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10000;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `known_users`
--
ALTER TABLE `known_users`
  MODIFY `user_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=655;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `media_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7933;

--
-- AUTO_INCREMENT for table `media_configurations`
--
ALTER TABLE `media_configurations`
  MODIFY `config_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uploads`
--
ALTER TABLE `uploads`
  MODIFY `upload_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10983;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `app_locking`
--
ALTER TABLE `app_locking`
  ADD CONSTRAINT `app_locking_ibfk_1` FOREIGN KEY (`locked_by`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `app_locking_messages`
--
ALTER TABLE `app_locking_messages`
  ADD CONSTRAINT `app_locking_messages_ibfk_1` FOREIGN KEY (`lock_id`) REFERENCES `app_locking` (`lock_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `app_locking_messages_ibfk_2` FOREIGN KEY (`message_id`) REFERENCES `app_messaging` (`message_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `app_locking_messages_ibfk_3` FOREIGN KEY (`requested_by`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `app_messagelog`
--
ALTER TABLE `app_messagelog`
  ADD CONSTRAINT `app_messagelog_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `app_messaging`
--
ALTER TABLE `app_messaging`
  ADD CONSTRAINT `app_messaging_ibfk_1` FOREIGN KEY (`from_user`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `app_messaging_ibfk_2` FOREIGN KEY (`to_user`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `app_messaging_ibfk_3` FOREIGN KEY (`in_reply_to`) REFERENCES `app_messaging` (`message_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `app_messaging_ibfk_4` FOREIGN KEY (`lock_id`) REFERENCES `app_locking` (`lock_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `uploads`
--
ALTER TABLE `uploads`
  ADD CONSTRAINT `uploads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
