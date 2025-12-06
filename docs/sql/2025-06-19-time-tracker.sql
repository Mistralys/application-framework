-- ------------------------------------------------------------
-- TIME SPAN MANAGEMENT TABLE
-- ------------------------------------------------------------
--

START TRANSACTION;

--
-- Table structure for table `time_tracker_entries`
--

CREATE TABLE `time_tracker_entries` (
    `time_entry_id` int(11) UNSIGNED NOT NULL,
    `user_id` int(11) UNSIGNED NOT NULL,
    `date` date NOT NULL,
    `time_start` time DEFAULT NULL,
    `time_end` time DEFAULT NULL,
    `duration` int(11) UNSIGNED NOT NULL,
    `type` varchar(40) NOT NULL,
    `ticket` varchar(160) NOT NULL,
    `ticket_url` text NOT NULL DEFAULT '',
    `processed` enum('yes','no') NOT NULL DEFAULT 'no',
    `comments` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `time_tracker_entry_data`
--

CREATE TABLE `time_tracker_entry_data` (
    `time_entry_id` int(11) UNSIGNED NOT NULL,
    `name` varchar(180) NOT NULL,
    `value` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `time_tracker_time_spans`
--

CREATE TABLE `time_tracker_time_spans` (
    `time_span_id` int(11) UNSIGNED NOT NULL,
    `user_id` int(11) UNSIGNED NOT NULL,
    `type` varchar(40) NOT NULL,
    `date_start` date NOT NULL,
    `date_end` date NOT NULL,
    `days` int(11) UNSIGNED NOT NULL,
    `processed` enum('yes','no') NOT NULL DEFAULT 'no',
    `comments` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `time_tracker_types`
--

CREATE TABLE `time_tracker_types` (
    `time_type_id` int(11) UNSIGNED NOT NULL,
    `label` varchar(160) NOT NULL,
    `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for table `time_tracker_entries`
--
ALTER TABLE `time_tracker_entries`
    ADD PRIMARY KEY (`time_entry_id`),
    ADD KEY `user_id` (`user_id`),
    ADD KEY `date` (`date`),
    ADD KEY `type` (`type`),
    ADD KEY `ticket` (`ticket`),
    ADD KEY `duration` (`duration`),
    ADD KEY `processed` (`processed`);

--
-- Indexes for table `time_tracker_entry_data`
--
ALTER TABLE `time_tracker_entry_data`
    ADD KEY `time_entry_id` (`time_entry_id`),
    ADD KEY `name` (`name`);

--
-- Indexes for table `time_tracker_time_spans`
--
ALTER TABLE `time_tracker_time_spans`
    ADD PRIMARY KEY (`time_span_id`),
    ADD KEY `user_id` (`user_id`),
    ADD KEY `date_end` (`date_end`),
    ADD KEY `date_start` (`date_start`),
    ADD KEY `processed` (`processed`),
    ADD KEY `type` (`type`),
    ADD KEY `days` (`days`);

--
-- Indexes for table `time_tracker_types`
--
ALTER TABLE `time_tracker_types`
    ADD PRIMARY KEY (`time_type_id`),
    ADD KEY `label` (`label`);

--
-- Constraints for table `time_tracker_entry_data`
--
ALTER TABLE `time_tracker_entry_data`
    ADD CONSTRAINT `time_tracker_entry_data_ibfk_1` FOREIGN KEY (`time_entry_id`) REFERENCES `time_tracker_entries` (`time_entry_id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;
