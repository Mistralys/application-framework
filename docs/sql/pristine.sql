-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 13, 2024 at 09:07 AM
-- Server version: 10.5.22-MariaDB
-- PHP Version: 8.3.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `application_framework`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_locking_messages`
--

CREATE TABLE `app_locking_messages` (
    `lock_id` bigint(11) UNSIGNED NOT NULL,
    `requested_by` int(11) UNSIGNED NOT NULL,
    `message_id` bigint(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_news`
--

CREATE TABLE `app_news` (
    `news_id` int(11) UNSIGNED NOT NULL,
    `parent_news_id` int(11) UNSIGNED DEFAULT NULL,
    `news_type` varchar(60) NOT NULL,
    `label` varchar(120) NOT NULL,
    `author` int(11) UNSIGNED NOT NULL,
    `locale` varchar(5) NOT NULL,
    `status` varchar(20) NOT NULL DEFAULT 'draft',
    `synopsis` text NOT NULL DEFAULT '',
    `article` mediumtext NOT NULL DEFAULT '',
    `date_created` datetime NOT NULL,
    `date_modified` datetime NOT NULL,
    `criticality` varchar(60) DEFAULT NULL COMMENT 'Used for alerts.',
    `scheduled_from_date` datetime DEFAULT NULL,
    `scheduled_to_date` datetime DEFAULT NULL,
    `requires_receipt` enum('yes','no') NOT NULL DEFAULT 'no' COMMENT 'Used for alerts.',
    `views` int(11) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_news_categories`
--

CREATE TABLE `app_news_categories` (
    `news_category_id` int(11) UNSIGNED NOT NULL,
    `label` varchar(160) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_news_entry_categories`
--

CREATE TABLE `app_news_entry_categories` (
    `news_id` int(11) UNSIGNED NOT NULL,
    `news_category_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_news_reactions`
--

CREATE TABLE `app_news_reactions` (
    `reaction_id` int(11) UNSIGNED NOT NULL,
    `label` varchar(60) NOT NULL,
    `emoji` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_news_related`
--

CREATE TABLE `app_news_related` (
    `news_id` int(11) UNSIGNED NOT NULL,
    `related_news_id` int(11) UNSIGNED NOT NULL,
    `relation_type` varchar(160) NOT NULL,
    `relation_params` text NOT NULL COMMENT 'JSON configuration.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_news_user_reactions`
--

CREATE TABLE `app_news_user_reactions` (
    `news_id` int(11) UNSIGNED NOT NULL,
    `user_id` int(11) UNSIGNED NOT NULL,
    `reaction_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_news_user_receipts`
--

CREATE TABLE `app_news_user_receipts` (
    `news_id` int(11) UNSIGNED NOT NULL,
    `user_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Stores user ratings for application screens.';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Unique application screens rated by users';

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
    `data_key` varchar(80) NOT NULL,
    `data_value` mediumtext NOT NULL,
    `data_role` enum('cache','persistent') NOT NULL DEFAULT 'cache'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
    `country_id` int(11) UNSIGNED NOT NULL,
    `iso` varchar(2) NOT NULL,
    `label` varchar(180) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Cache for Editor countries';

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
-- Table structure for table `custom_properties`
--

CREATE TABLE `custom_properties` (
    `property_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `known_users`
--

INSERT INTO `known_users` (`user_id`, `foreign_id`, `firstname`, `lastname`, `email`) VALUES
    (1, '__system', 'Application Framework', 'Application', 'system@app-framework.ui'),
    (2, '__dummy', 'Dummy', 'User', 'someone@app-framework.ui');

-- --------------------------------------------------------

--
-- Table structure for table `locales_application`
--

CREATE TABLE `locales_application` (
    `locale_name` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locales_content`
--

CREATE TABLE `locales_content` (
    `locale_name` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
    `media_extension` varchar(20) NOT NULL,
    `file_size` int(11) UNSIGNED NOT NULL DEFAULT 0,
    `keywords` varchar(500) NOT NULL DEFAULT '',
    `description` varchar(1200) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_configurations`
--

CREATE TABLE `media_configurations` (
    `config_id` int(11) UNSIGNED NOT NULL,
    `type_id` varchar(60) NOT NULL,
    `config_key` varchar(32) NOT NULL,
    `config` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_tags`
--

CREATE TABLE `media_tags` (
    `media_id` int(11) UNSIGNED NOT NULL,
    `tag_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Connects media documents with tags.';

-- --------------------------------------------------------

--
-- Table structure for table `revisionables`
--

CREATE TABLE `revisionables` (
    `revisionable_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `revisionables_changelog`
--

CREATE TABLE `revisionables_changelog` (
    `changelog_id` int(11) UNSIGNED NOT NULL,
    `revisionable_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `revisionables_revisions`
--

CREATE TABLE `revisionables_revisions` (
    `revisionable_id` int(11) UNSIGNED NOT NULL,
    `revisionable_revision` int(11) UNSIGNED NOT NULL,
    `pretty_revision` int(11) UNSIGNED NOT NULL,
    `label` varchar(160) NOT NULL,
    `alias` varchar(160) NOT NULL,
    `state` enum('draft','finalized','inactive','deleted') NOT NULL DEFAULT 'draft',
    `date` datetime NOT NULL,
    `author` int(11) UNSIGNED NOT NULL,
    `comments` text NOT NULL,
    `structural` varchar(400) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
    `tag_id` int(11) UNSIGNED NOT NULL,
    `label` varchar(160) NOT NULL,
    `parent_tag_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tags_registry`
--

CREATE TABLE `tags_registry` (
    `tag_id` int(11) UNSIGNED NOT NULL,
    `registry_key` varchar(180) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tags_translations`
--

CREATE TABLE `tags_translations` (
    `tag_id` int(11) UNSIGNED NOT NULL,
    `locale_name` varchar(5) NOT NULL,
    `locale_label` varchar(160) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Stores tag label translations.';

-- --------------------------------------------------------

--
-- Table structure for table `test_records`
--

CREATE TABLE `test_records` (
    `record_id` int(11) UNSIGNED NOT NULL,
    `label` varchar(180) NOT NULL,
    `alias` varchar(160) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `test_records_data`
--

CREATE TABLE `test_records_data` (
    `record_id` int(11) UNSIGNED NOT NULL,
    `name` varchar(250) NOT NULL,
    `value` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `test_records_tags`
--

CREATE TABLE `test_records_tags` (
    `record_id` int(11) UNSIGNED NOT NULL,
    `tag_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uploads`
--

CREATE TABLE `uploads` (
    `upload_id` int(11) UNSIGNED NOT NULL,
    `user_id` int(11) UNSIGNED NOT NULL,
    `upload_date` datetime NOT NULL,
    `upload_name` varchar(240) NOT NULL,
    `upload_extension` varchar(20) NOT NULL,
    `media_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_emails`
--

CREATE TABLE `user_emails` (
    `user_id` int(11) UNSIGNED NOT NULL,
    `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
    `user_id` int(11) UNSIGNED NOT NULL,
    `setting_name` varchar(180) NOT NULL,
    `setting_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
-- Indexes for table `app_news`
--
ALTER TABLE `app_news`
    ADD PRIMARY KEY (`news_id`),
    ADD KEY `label` (`label`),
    ADD KEY `date_created` (`date_created`),
    ADD KEY `criticality` (`criticality`),
    ADD KEY `visible_from_date` (`scheduled_from_date`),
    ADD KEY `visible_to_date` (`scheduled_to_date`),
    ADD KEY `dismissable` (`requires_receipt`),
    ADD KEY `author` (`author`),
    ADD KEY `date_modified` (`date_modified`),
    ADD KEY `parent_news_id` (`parent_news_id`),
    ADD KEY `views` (`views`),
    ADD KEY `status` (`status`),
    ADD KEY `news_type` (`news_type`),
    ADD KEY `locale` (`locale`);

--
-- Indexes for table `app_news_categories`
--
ALTER TABLE `app_news_categories`
    ADD PRIMARY KEY (`news_category_id`),
    ADD KEY `label` (`label`);

--
-- Indexes for table `app_news_entry_categories`
--
ALTER TABLE `app_news_entry_categories`
    ADD PRIMARY KEY (`news_id`,`news_category_id`),
    ADD KEY `news_id` (`news_id`),
    ADD KEY `news_category_id` (`news_category_id`);

--
-- Indexes for table `app_news_reactions`
--
ALTER TABLE `app_news_reactions`
    ADD PRIMARY KEY (`reaction_id`),
    ADD KEY `label` (`label`),
    ADD KEY `emoji` (`emoji`);

--
-- Indexes for table `app_news_related`
--
ALTER TABLE `app_news_related`
    ADD PRIMARY KEY (`news_id`,`related_news_id`,`relation_type`),
    ADD KEY `news_id` (`news_id`),
    ADD KEY `related_news_id` (`related_news_id`),
    ADD KEY `relation_type` (`relation_type`);

--
-- Indexes for table `app_news_user_reactions`
--
ALTER TABLE `app_news_user_reactions`
    ADD PRIMARY KEY (`news_id`,`user_id`,`reaction_id`),
    ADD KEY `user_id` (`user_id`),
    ADD KEY `reaction_id` (`reaction_id`);

--
-- Indexes for table `app_news_user_receipts`
--
ALTER TABLE `app_news_user_receipts`
    ADD PRIMARY KEY (`news_id`,`user_id`),
    ADD KEY `news_id` (`news_id`),
    ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `countries`
--
ALTER TABLE `countries`
    ADD PRIMARY KEY (`country_id`),
    ADD UNIQUE KEY `iso` (`iso`);

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
    ADD KEY `user_id` (`user_id`,`media_type`),
    ADD KEY `file_size` (`file_size`);

--
-- Indexes for table `media_configurations`
--
ALTER TABLE `media_configurations`
    ADD PRIMARY KEY (`config_id`),
    ADD KEY `type_id` (`type_id`,`config_key`);

--
-- Indexes for table `media_tags`
--
ALTER TABLE `media_tags`
    ADD PRIMARY KEY (`media_id`,`tag_id`),
    ADD KEY `media_id` (`media_id`),
    ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `revisionables`
--
ALTER TABLE `revisionables`
    ADD PRIMARY KEY (`revisionable_id`);

--
-- Indexes for table `revisionables_changelog`
--
ALTER TABLE `revisionables_changelog`
    ADD PRIMARY KEY (`changelog_id`),
    ADD KEY `revisionable_id` (`revisionable_id`);

--
-- Indexes for table `revisionables_revisions`
--
ALTER TABLE `revisionables_revisions`
    ADD PRIMARY KEY (`revisionable_revision`),
    ADD KEY `revisionable_id` (`revisionable_id`),
    ADD KEY `label` (`label`),
    ADD KEY `alias` (`alias`),
    ADD KEY `state` (`state`),
    ADD KEY `date` (`date`),
    ADD KEY `author` (`author`),
    ADD KEY `pretty_revision` (`pretty_revision`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
    ADD PRIMARY KEY (`tag_id`),
    ADD KEY `label` (`label`),
    ADD KEY `parent_tag_id` (`parent_tag_id`);

--
-- Indexes for table `tags_registry`
--
ALTER TABLE `tags_registry`
    ADD PRIMARY KEY (`registry_key`),
    ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `tags_translations`
--
ALTER TABLE `tags_translations`
    ADD KEY `tag_id` (`tag_id`),
    ADD KEY `locale_name` (`locale_name`),
    ADD KEY `locale_label` (`locale_label`);

--
-- Indexes for table `test_records`
--
ALTER TABLE `test_records`
    ADD PRIMARY KEY (`record_id`),
    ADD KEY `label` (`label`),
    ADD KEY `alias` (`alias`);

--
-- Indexes for table `test_records_data`
--
ALTER TABLE `test_records_data`
    ADD PRIMARY KEY (`record_id`,`name`),
    ADD KEY `record_id` (`record_id`),
    ADD KEY `name` (`name`);

--
-- Indexes for table `test_records_tags`
--
ALTER TABLE `test_records_tags`
    ADD PRIMARY KEY (`record_id`,`tag_id`),
    ADD KEY `record_id` (`record_id`),
    ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `uploads`
--
ALTER TABLE `uploads`
    ADD PRIMARY KEY (`upload_id`),
    ADD KEY `user_id` (`user_id`),
    ADD KEY `media_id` (`media_id`);

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
-- AUTO_INCREMENT for table `app_news`
--
ALTER TABLE `app_news`
    MODIFY `news_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_news_categories`
--
ALTER TABLE `app_news_categories`
    MODIFY `news_category_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_news_reactions`
--
ALTER TABLE `app_news_reactions`
    MODIFY `reaction_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `revisionables`
--
ALTER TABLE `revisionables`
    MODIFY `revisionable_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `revisionables_changelog`
--
ALTER TABLE `revisionables_changelog`
    MODIFY `changelog_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `revisionables_revisions`
--
ALTER TABLE `revisionables_revisions`
    MODIFY `revisionable_revision` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
    MODIFY `tag_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `test_records`
--
ALTER TABLE `test_records`
    MODIFY `record_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `app_news`
--
ALTER TABLE `app_news`
    ADD CONSTRAINT `app_news_ibfk_1` FOREIGN KEY (`author`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `app_news_ibfk_2` FOREIGN KEY (`parent_news_id`) REFERENCES `app_news` (`news_id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `app_news_entry_categories`
--
ALTER TABLE `app_news_entry_categories`
    ADD CONSTRAINT `app_news_entry_categories_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `app_news` (`news_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `app_news_entry_categories_ibfk_2` FOREIGN KEY (`news_category_id`) REFERENCES `app_news_categories` (`news_category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `app_news_related`
--
ALTER TABLE `app_news_related`
    ADD CONSTRAINT `app_news_related_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `app_news` (`news_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `app_news_related_ibfk_2` FOREIGN KEY (`related_news_id`) REFERENCES `app_news` (`news_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `app_news_user_reactions`
--
ALTER TABLE `app_news_user_reactions`
    ADD CONSTRAINT `app_news_user_reactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `app_news_user_reactions_ibfk_2` FOREIGN KEY (`news_id`) REFERENCES `app_news` (`news_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `app_news_user_reactions_ibfk_3` FOREIGN KEY (`reaction_id`) REFERENCES `app_news_reactions` (`reaction_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `app_news_user_receipts`
--
ALTER TABLE `app_news_user_receipts`
    ADD CONSTRAINT `app_news_user_receipts_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `app_news` (`news_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `app_news_user_receipts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `app_ratings`
--
ALTER TABLE `app_ratings`
    ADD CONSTRAINT `app_ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `app_ratings_ibfk_2` FOREIGN KEY (`rating_screen_id`) REFERENCES `app_ratings_screens` (`rating_screen_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `media`
--
ALTER TABLE `media`
    ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `media_tags`
--
ALTER TABLE `media_tags`
    ADD CONSTRAINT `media_tags_ibfk_1` FOREIGN KEY (`media_id`) REFERENCES `media` (`media_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `media_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `revisionables_revisions`
--
ALTER TABLE `revisionables_revisions`
    ADD CONSTRAINT `revisionables_revisions_ibfk_1` FOREIGN KEY (`revisionable_id`) REFERENCES `revisionables` (`revisionable_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `revisionables_revisions_ibfk_2` FOREIGN KEY (`author`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tags_translations`
--
ALTER TABLE `tags_translations`
    ADD CONSTRAINT `tags_translations_ibfk_1` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `tags_translations_ibfk_2` FOREIGN KEY (`locale_name`) REFERENCES `locales_application` (`locale_name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `test_records_data`
--
ALTER TABLE `test_records_data`
    ADD CONSTRAINT `test_records_data_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `test_records` (`record_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `test_records_tags`
--
ALTER TABLE `test_records_tags`
    ADD CONSTRAINT `test_records_tags_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `test_records` (`record_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `test_records_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `uploads`
--
ALTER TABLE `uploads`
    ADD CONSTRAINT `uploads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `uploads_ibfk_2` FOREIGN KEY (`media_id`) REFERENCES `media` (`media_id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `user_emails`
--
ALTER TABLE `user_emails`
    ADD CONSTRAINT `user_emails_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
    ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
