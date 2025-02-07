SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

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
    `comments` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `time_tracker_entries`
--
ALTER TABLE `time_tracker_entries`
    ADD PRIMARY KEY (`time_entry_id`),
    ADD KEY `user_id` (`user_id`),
    ADD KEY `date` (`date`),
    ADD KEY `type` (`type`),
    ADD KEY `ticket` (`ticket`),
    ADD KEY `duration` (`duration`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `time_tracker_entries`
--
ALTER TABLE `time_tracker_entries`
    MODIFY `time_entry_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;
