-- Adds revisionable tables for the Test Driver's revisionable classes.

-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 25, 2024 at 06:21 AM
-- Server version: 10.5.22-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `application_framework`
--

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
-- Table structure for table `revisionables_current_revisions`
--

CREATE TABLE `revisionables_current_revisions` (
    `revisionable_id` int(11) UNSIGNED NOT NULL,
    `current_revision` int(11) UNSIGNED NOT NULL
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
    `state` enum('draft','finalized','inactive','deleted') NOT NULL DEFAULT 'draft',
    `date` datetime NOT NULL,
    `author` int(11) UNSIGNED NOT NULL,
    `comments` text NOT NULL,
    `structural` VARCHAR(400) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

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
-- Indexes for table `revisionables_current_revisions`
--
ALTER TABLE `revisionables_current_revisions`
ADD KEY `revisionable_id` (`revisionable_id`),
ADD KEY `revisionable_revision` (`current_revision`);

--
-- Indexes for table `revisionables_revisions`
--
ALTER TABLE `revisionables_revisions`
ADD PRIMARY KEY (`revisionable_revision`),
ADD KEY `revisionable_id` (`revisionable_id`),
ADD KEY `label` (`label`),
ADD KEY `state` (`state`),
ADD KEY `date` (`date`),
ADD KEY `author` (`author`),
ADD KEY `pretty_revision` (`pretty_revision`);

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
-- Constraints for dumped tables
--

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
COMMIT;
