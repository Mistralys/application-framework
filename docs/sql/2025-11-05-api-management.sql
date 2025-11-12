-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 05, 2025 at 04:06 PM
-- Server version: 12.0.2-MariaDB
-- PHP Version: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `application_framework`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_changelog`
--

CREATE TABLE `api_changelog` (
    `changelog_id` bigint(11) UNSIGNED NOT NULL,
    `changelog_date` datetime NOT NULL,
    `changelog_type` varchar(180) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    `changelog_author` int(11) UNSIGNED NOT NULL,
    `changelog_data` text NOT NULL,
    `api_client_id` int(11) UNSIGNED NOT NULL,
    `api_key_id` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_clients`
--

CREATE TABLE `api_clients` (
    `api_client_id` int(11) UNSIGNED NOT NULL,
    `label` varchar(180) NOT NULL,
    `foreign_id` varchar(180) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    `date_created` datetime NOT NULL DEFAULT current_timestamp(),
    `created_by` int(11) UNSIGNED NOT NULL,
    `is_active` enum('yes','no') CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'yes',
    `comments` text NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
    `api_key_id` int(11) UNSIGNED NOT NULL,
    `api_client_id` int(11) UNSIGNED NOT NULL,
    `api_key` varchar(120) NOT NULL,
    `pseudo_user` int(11) UNSIGNED NOT NULL,
    `label` varchar(180) NOT NULL,
    `comments` text NOT NULL DEFAULT '',
    `grant_all_methods` enum('yes','no') CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'no',
    `date_created` datetime NOT NULL DEFAULT current_timestamp(),
    `created_by` int(11) UNSIGNED NOT NULL,
    `expiry_delay` varchar(180) DEFAULT NULL,
    `expiry_date` datetime DEFAULT NULL,
    `expired` enum('yes','no') NOT NULL DEFAULT 'no',
    `last_used` datetime DEFAULT NULL,
    `usage_count` int(11) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_key_methods`
--

CREATE TABLE `api_key_methods` (
    `api_key_id` int(11) UNSIGNED NOT NULL,
    `api_client_id` int(11) UNSIGNED NOT NULL,
    `method_name` varchar(180) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='List of API method names allowed to be used with this API key.';

-- --------------------------------------------------------

--
-- Table structure for table `api_requests`
--

CREATE TABLE `api_requests` (
    `api_request_id` int(11) UNSIGNED NOT NULL,
    `api_key_id` int(11) UNSIGNED DEFAULT NULL,
    `api_client_id` int(11) UNSIGNED DEFAULT NULL,
    `app_instance` varchar(180) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL COMMENT 'Name of the application instance that served the request',
    `request_time` datetime NOT NULL DEFAULT current_timestamp(),
    `method_name` varchar(180) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL COMMENT 'The requested API method name',
    `app_locale` varchar(5) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    `http_method` enum('get','post','put','delete') CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    `status_code` int(4) UNSIGNED NOT NULL,
    `status_name` enum('success','error') CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    `duration_ms` int(11) UNSIGNED NOT NULL,
    `source_ip` varchar(60) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    `user_agent` varchar(220) NOT NULL,
    `error_code` int(11) UNSIGNED DEFAULT NULL,
    `error_message` varchar(200) NOT NULL,
    `request_params` text NOT NULL,
    `response_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `api_changelog`
--
ALTER TABLE `api_changelog`
    ADD PRIMARY KEY (`changelog_id`),
    ADD KEY `changelog_date` (`changelog_date`),
    ADD KEY `changelog_type` (`changelog_type`),
    ADD KEY `changelog_author` (`changelog_author`),
    ADD KEY `api_client_id` (`api_client_id`),
    ADD KEY `api_key_id` (`api_key_id`);

--
-- Indexes for table `api_clients`
--
ALTER TABLE `api_clients`
    ADD PRIMARY KEY (`api_client_id`),
    ADD KEY `label` (`label`),
    ADD KEY `foreignID` (`foreign_id`),
    ADD KEY `date_created` (`date_created`),
    ADD KEY `created_by` (`created_by`),
    ADD KEY `is_active` (`is_active`);

--
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
    ADD PRIMARY KEY (`api_key_id`),
    ADD KEY `api_client_id` (`api_client_id`),
    ADD KEY `key` (`api_key`),
    ADD KEY `label` (`label`),
    ADD KEY `all_methods` (`grant_all_methods`),
    ADD KEY `date_created` (`date_created`),
    ADD KEY `expiry_delay` (`expiry_delay`),
    ADD KEY `expiry_date` (`expiry_date`),
    ADD KEY `created_by` (`created_by`),
    ADD KEY `expired` (`expired`),
    ADD KEY `last_used` (`last_used`),
    ADD KEY `usage_count` (`usage_count`),
    ADD KEY `pseudo_user` (`pseudo_user`);

--
-- Indexes for table `api_key_methods`
--
ALTER TABLE `api_key_methods`
    ADD PRIMARY KEY (`api_key_id`,`method_name`),
    ADD KEY `method_name` (`method_name`),
    ADD KEY `api_client_id` (`api_client_id`),
    ADD KEY `api_key_id` (`api_key_id`);

--
-- Indexes for table `api_requests`
--
ALTER TABLE `api_requests`
    ADD PRIMARY KEY (`api_request_id`),
    ADD KEY `request_time` (`request_time`),
    ADD KEY `api_key_id` (`api_key_id`),
    ADD KEY `api_client_id` (`api_client_id`),
    ADD KEY `method_name` (`method_name`),
    ADD KEY `app_instance` (`app_instance`),
    ADD KEY `app_locale` (`app_locale`),
    ADD KEY `http_method` (`http_method`),
    ADD KEY `status_code` (`status_code`),
    ADD KEY `status_name` (`status_name`),
    ADD KEY `duration_ms` (`duration_ms`),
    ADD KEY `source_ip` (`source_ip`),
    ADD KEY `user_agent` (`user_agent`),
    ADD KEY `error_code` (`error_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `api_changelog`
--
ALTER TABLE `api_changelog`
    MODIFY `changelog_id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `api_clients`
--
ALTER TABLE `api_clients`
    MODIFY `api_client_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `api_keys`
--
ALTER TABLE `api_keys`
    MODIFY `api_key_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `api_requests`
--
ALTER TABLE `api_requests`
    MODIFY `api_request_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `api_keys`
--
ALTER TABLE `api_keys`
    ADD CONSTRAINT `api_keys_ibfk_1` FOREIGN KEY (`api_client_id`) REFERENCES `api_clients` (`api_client_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `api_keys_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `api_keys_ibfk_3` FOREIGN KEY (`pseudo_user`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `api_key_methods`
--
ALTER TABLE `api_key_methods`
    ADD CONSTRAINT `api_key_methods_ibfk_1` FOREIGN KEY (`api_client_id`) REFERENCES `api_clients` (`api_client_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `api_key_methods_ibfk_2` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`api_key_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
