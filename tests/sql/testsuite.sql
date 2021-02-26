-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 20, 2018 at 09:55 AM
-- Server version: 10.3.10-MariaDB
-- PHP Version: 7.2.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

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
  `response` text DEFAULT NULL,
  `custom_data` text DEFAULT NULL,
  `lock_id` bigint(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `app_ratings`
--

CREATE TABLE `app_ratings` (
  `rating_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `rating_screen_id` int(11) UNSIGNED NOT NULL,
  `date` datetime NOT NULL,
  `rating` int(11) NOT NULL,
  `comments` text NOT NULL,
  `app_version` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores user ratings for application screens.';

-- --------------------------------------------------------

--
-- Table structure for table `app_ratings_screens`
--

CREATE TABLE `app_ratings_screens` (
  `rating_screen_id` int(11) UNSIGNED NOT NULL,
  `hash` varchar(32) NOT NULL,
  `dispatcher` varchar(250) NOT NULL,
  `path` varchar(250) NOT NULL,
  `params` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Unique application screens rated by users';

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
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `campaign_id` int(11) UNSIGNED NOT NULL,
  `campaign_label` varchar(160) NOT NULL,
  `campaign_alias` varchar(160) DEFAULT NULL,
  `campaign_created` datetime NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 1
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

-- --------------------------------------------------------

--
-- Table structure for table `custom_properties`
--

CREATE TABLE `custom_properties` (
  `property_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `custom_properties_data`
--

CREATE TABLE `custom_properties_data` (
  `property_id` int(11) UNSIGNED NOT NULL,
  `owner_type` varchar(250) NOT NULL,
  `owner_key` varchar(250) NOT NULL,
  `name` varchar(180) NOT NULL,
  `is_structural` enum('yes','no') NOT NULL DEFAULT 'no',
  `value` text NOT NULL,
  `label` varchar(180) NOT NULL,
  `default_value` text NOT NULL,
  `preset_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `custom_properties_presets`
--

CREATE TABLE `custom_properties_presets` (
  `preset_id` int(11) UNSIGNED NOT NULL,
  `owner_type` varchar(250) NOT NULL,
  `editable` enum('yes','no') NOT NULL DEFAULT 'yes',
  `name` varchar(180) NOT NULL,
  `is_structural` enum('yes','no') NOT NULL DEFAULT 'no',
  `label` varchar(180) NOT NULL,
  `default_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

-- --------------------------------------------------------

--
-- Table structure for table `locales_application`
--

CREATE TABLE `locales_application` (
  `locale_name` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `locales_content`
--

CREATE TABLE `locales_content` (
  `locale_name` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
-- Table structure for table `user_emails`
--

CREATE TABLE `user_emails` (
   `user_id` int(11) UNSIGNED NOT NULL,
   `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

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
-- Indexes for table `app_ratings`
--
ALTER TABLE `app_ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `rating` (`rating`),
  ADD KEY `date` (`date`),
  ADD KEY `rating_screen_id` (`rating_screen_id`),
  ADD KEY `app_version` (`app_version`);

--
-- Indexes for table `app_ratings_screens`
--
ALTER TABLE `app_ratings_screens`
  ADD PRIMARY KEY (`rating_screen_id`),
  ADD UNIQUE KEY `hash` (`hash`),
  ADD KEY `dispatcher` (`dispatcher`),
  ADD KEY `path` (`path`);

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`data_key`),
  ADD KEY `data_role` (`data_role`);

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`campaign_id`),
  ADD KEY `campaign_created` (`campaign_created`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `campaign_alias` (`campaign_alias`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`country_id`),
  ADD KEY `iso` (`iso`);

--
-- Indexes for table `custom_properties`
--
ALTER TABLE `custom_properties`
  ADD PRIMARY KEY (`property_id`);

--
-- Indexes for table `custom_properties_data`
--
ALTER TABLE `custom_properties_data`
  ADD PRIMARY KEY (`property_id`,`owner_type`,`owner_key`),
  ADD KEY `is_structural` (`is_structural`),
  ADD KEY `name` (`name`),
  ADD KEY `product_id` (`owner_type`),
  ADD KEY `featuretable_revision` (`owner_key`),
  ADD KEY `preset_number` (`preset_id`);

--
-- Indexes for table `custom_properties_presets`
--
ALTER TABLE `custom_properties_presets`
  ADD PRIMARY KEY (`preset_id`),
  ADD KEY `editable` (`editable`),
  ADD KEY `name` (`name`),
  ADD KEY `is_structural` (`is_structural`),
  ADD KEY `owner_type` (`owner_type`);

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
-- Indexes for table `user_emails`
--
ALTER TABLE `user_emails`
  ADD PRIMARY KEY (`user_id`,`email`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `email` (`email`);

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
  MODIFY `lock_id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_messagelog`
--
ALTER TABLE `app_messagelog`
  MODIFY `log_id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_messaging`
--
ALTER TABLE `app_messaging`
  MODIFY `message_id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_ratings`
--
ALTER TABLE `app_ratings`
  MODIFY `rating_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_ratings_screens`
--
ALTER TABLE `app_ratings_screens`
  MODIFY `rating_screen_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `campaign_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `country_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_properties`
--
ALTER TABLE `custom_properties`
  MODIFY `property_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_properties_presets`
--
ALTER TABLE `custom_properties_presets`
  MODIFY `preset_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `known_users`
--
ALTER TABLE `known_users`
  MODIFY `user_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `media_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_configurations`
--
ALTER TABLE `media_configurations`
  MODIFY `config_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uploads`
--
ALTER TABLE `uploads`
  MODIFY `upload_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `app_ratings`
--
ALTER TABLE `app_ratings`
  ADD CONSTRAINT `app_ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `app_ratings_ibfk_2` FOREIGN KEY (`rating_screen_id`) REFERENCES `app_ratings_screens` (`rating_screen_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD CONSTRAINT `campaigns_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Constraints for table `user_emails`
--
ALTER TABLE `user_emails`
  ADD CONSTRAINT `user_emails_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

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

INSERT INTO `locales_application` (`locale_name`) VALUES
('de_DE'),
('en_UK');

INSERT INTO `locales_content` (`locale_name`) VALUES
('de_DE'),
('en_UK');

INSERT INTO `known_users` (`user_id`, `foreign_id`, `firstname`, `lastname`, `email`) VALUES
(1, '__system', 'SPIN', 'System', 's.mordziol@mistralys.eu'),
(2, '1234567890', 'Otto', 'Mustermann', 's.mordziol@mistralys.eu'),
(3, '21225378', 'Sebastian', 'Mordziol', 's.mordziol@mistralys.com');