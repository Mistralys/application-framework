-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 20, 2024 at 06:05 PM
-- Server version: 10.3.10-MariaDB-log
-- PHP Version: 8.2.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `app_framework_testsuite`
--

-- --------------------------------------------------------

--
-- Table structure for table `media_tags`
--

CREATE TABLE `media_tags` (
`media_id` int(11) UNSIGNED NOT NULL,
`tag_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Connects media documents with tags.';

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
`tag_id` int(11) UNSIGNED NOT NULL,
`label` varchar(160) NOT NULL,
`parent_tag_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tags_registry`
--

CREATE TABLE `tags_registry` (
`tag_id` int(11) UNSIGNED NOT NULL,
`registry_key` varchar(180) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `test_records_tags`
--

CREATE TABLE `test_records_tags` (
`record_id` int(11) UNSIGNED NOT NULL,
`tag_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
MODIFY `tag_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `test_records_tags`
--
ALTER TABLE `test_records_tags`
ADD CONSTRAINT `test_records_tags_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `test_records` (`record_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `test_records_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
