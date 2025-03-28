SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

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

CREATE TABLE `time_tracker_entry_data` (
    `time_entry_id` int(11) UNSIGNED NOT NULL,
    `name` varchar(180) NOT NULL,
    `value` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `time_tracker_entries`
    ADD PRIMARY KEY (`time_entry_id`),
    ADD KEY `user_id` (`user_id`),
    ADD KEY `date` (`date`),
    ADD KEY `type` (`type`),
    ADD KEY `ticket` (`ticket`),
    ADD KEY `duration` (`duration`);

ALTER TABLE `time_tracker_entry_data`
    ADD KEY `time_entry_id` (`time_entry_id`),
    ADD KEY `name` (`name`);

ALTER TABLE `time_tracker_entries`
    MODIFY `time_entry_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `time_tracker_entry_data`
    ADD CONSTRAINT `time_tracker_entry_data_ibfk_1` FOREIGN KEY (`time_entry_id`) REFERENCES `time_tracker_entries` (`time_entry_id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;
