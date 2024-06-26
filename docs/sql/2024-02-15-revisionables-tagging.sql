-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 02, 2024 at 10:07 AM
-- Server version: 10.5.22-MariaDB
-- PHP Version: 8.3.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Adjust table character sets
--

ALTER TABLE `locales_application`
    CHANGE `locale_name` `locale_name` VARCHAR(5) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;

ALTER TABLE `locales_content`
    CHANGE `locale_name` `locale_name` VARCHAR(5) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;

--
-- Table structure for table `media_tags`
--

CREATE TABLE `media_tags` (
    `media_id` int(11) UNSIGNED NOT NULL,
    `tag_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Connects media documents with tags.';

-- --------------------------------------------------------

--
-- Table structure for table `revisionables`
--

CREATE TABLE `revisionables` (
    `revisionable_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `revisionables_changelog`
--

CREATE TABLE `revisionables_changelog` (
    `changelog_id` int(11) UNSIGNED NOT NULL,
    `revisionable_id` int(11) UNSIGNED NOT NULL,
    `revisionable_revision` int(11) UNSIGNED NOT NULL,
    `changelog_date` datetime NOT NULL,
    `changelog_author` int(11) UNSIGNED NOT NULL,
    `changelog_type` varchar(160) NOT NULL,
    `changelog_data` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `revisionables_current_revisions`
--

CREATE TABLE `revisionables_current_revisions` (
    `revisionable_id` int(11) UNSIGNED NOT NULL,
    `current_revision` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `revisionables_revisions_data`
--

CREATE TABLE `revisionables_revisions_data` (
    `revisionable_id` int(11) UNSIGNED NOT NULL,
    `revisionable_revision` int(11) UNSIGNED NOT NULL,
    `data_key` varchar(300) NOT NULL,
    `data_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
    `tag_id` int(11) UNSIGNED NOT NULL,
    `label` varchar(160) NOT NULL,
    `parent_tag_id` int(11) DEFAULT NULL,
    `sort_type` varchar(60) NOT NULL,
    `sort_weight` int(11) NOT NULL DEFAULT 0,
    `weight` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tags_registry`
--

CREATE TABLE `tags_registry` (
    `tag_id` int(11) UNSIGNED NOT NULL,
    `registry_key` varchar(180) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tags_translations`
--

CREATE TABLE `tags_translations` (
    `tag_id` int(11) UNSIGNED NOT NULL,
    `locale_name` varchar(5) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    `locale_label` varchar(160) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Stores tag label translations.';

-- --------------------------------------------------------

--
-- Table structure for table `test_records`
--

CREATE TABLE `test_records` (
    `record_id` int(11) UNSIGNED NOT NULL,
    `label` varchar(180) NOT NULL,
    `alias` varchar(160) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `test_records_data`
--

CREATE TABLE `test_records_data` (
    `record_id` int(11) UNSIGNED NOT NULL,
    `name` varchar(250) NOT NULL,
    `value` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `test_records_tags`
--

CREATE TABLE `test_records_tags` (
    `record_id` int(11) UNSIGNED NOT NULL,
    `tag_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

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
    ADD KEY `revisionable_id` (`revisionable_id`),
    ADD KEY `revisionable_revision` (`revisionable_revision`),
    ADD KEY `changelog_date` (`changelog_date`),
    ADD KEY `changelog_author` (`changelog_author`),
    ADD KEY `changelog_type` (`changelog_type`);

--
-- Indexes for table `revisionables_current_revisions`
--
ALTER TABLE `revisionables_current_revisions`
    ADD PRIMARY KEY (`revisionable_id`),
    ADD KEY `current_revision` (`current_revision`);

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
-- Indexes for table `revisionables_revisions_data`
--
ALTER TABLE `revisionables_revisions_data`
    ADD PRIMARY KEY (`revisionable_id`,`revisionable_revision`,`data_key`),
    ADD KEY `revisionable_id` (`revisionable_id`),
    ADD KEY `revision` (`revisionable_revision`),
    ADD KEY `data_key` (`data_key`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
    ADD PRIMARY KEY (`tag_id`),
    ADD KEY `label` (`label`),
    ADD KEY `parent_tag_id` (`parent_tag_id`),
    ADD KEY `sort_type` (`sort_type`),
    ADD KEY `sort_weight` (`sort_weight`),
    ADD KEY `weight` (`weight`);

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
-- AUTO_INCREMENT for dumped tables
--

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
-- Constraints for dumped tables
--

--
-- Constraints for table `media_tags`
--
ALTER TABLE `media_tags`
    ADD CONSTRAINT `media_tags_ibfk_1` FOREIGN KEY (`media_id`) REFERENCES `media` (`media_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `media_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `revisionables_changelog`
--
ALTER TABLE `revisionables_changelog`
    ADD CONSTRAINT `revisionables_changelog_ibfk_1` FOREIGN KEY (`revisionable_id`) REFERENCES `revisionables` (`revisionable_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `revisionables_changelog_ibfk_2` FOREIGN KEY (`revisionable_revision`) REFERENCES `revisionables_revisions` (`revisionable_revision`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `revisionables_current_revisions`
--
ALTER TABLE `revisionables_current_revisions`
    ADD CONSTRAINT `revisionables_current_revisions_ibfk_1` FOREIGN KEY (`revisionable_id`) REFERENCES `revisionables` (`revisionable_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `revisionables_current_revisions_ibfk_2` FOREIGN KEY (`current_revision`) REFERENCES `revisionables_revisions` (`revisionable_revision`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `revisionables_revisions`
--
ALTER TABLE `revisionables_revisions`
    ADD CONSTRAINT `revisionables_revisions_ibfk_1` FOREIGN KEY (`revisionable_id`) REFERENCES `revisionables` (`revisionable_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `revisionables_revisions_ibfk_2` FOREIGN KEY (`author`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `revisionables_revisions_data`
--
ALTER TABLE `revisionables_revisions_data`
    ADD CONSTRAINT `revisionables_revisions_data_ibfk_1` FOREIGN KEY (`revisionable_id`) REFERENCES `revisionables` (`revisionable_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `revisionables_revisions_data_ibfk_2` FOREIGN KEY (`revisionable_revision`) REFERENCES `revisionables_revisions` (`revisionable_revision`) ON DELETE CASCADE ON UPDATE CASCADE;

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

COMMIT;
